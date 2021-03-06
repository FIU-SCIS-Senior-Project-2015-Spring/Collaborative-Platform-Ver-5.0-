<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 5/25/2015
 * Time: 5:33 PM
 */
/*
 * Sets up the connection to look at the emails on the gmail account fiucoplat@cp-dev.cs.fiu.edu
 * and returns the connection
 */
DEFINE ("serverName", "cp-dev.cis.fiu.edu/coplat");

/*
 * Sets up the connection to the database that is running serverside
 * and returns the connection
 */
function establishDBConnection()
{
    $username = 'root';
    $password = '9Qst32+';
    $dbconn = new mysqli("localhost", $username, $password, "coplat");
    return $dbconn;
}
/*
 * Uses the email connection and the database connection,
 * Scans the emails on the email connection and then passes the relevant information to the detectOOOmessage
 * and the dectectBIOmessage functions
 * after which it removes the mentors from the away list that have been on the away list for longer than 24 hours
 * allowing them to be assigned tickets again
 */
function emailListener()
{

   // $connection = establishConnection();
    $dbConn = establishDBConnection();

    $path    = '/home/fiucoplat/Maildir/new';
    $files = scandir($path);
    foreach ($files as $afile)
    {
        $file = fopen($path."/".$afile,"r");
        $from = "";
        $subject = "";
        $body = "";
        $isbody = 0;
        $fromisSet = 0;
        $subjectisSet = 0;
        while($line = fgets($file))
        {
            //echo $line . "\n\n";
            if($isbody == 1)
            {
                $body = $body . $line;
            }

            if(strstr($line,"From: ") && strstr($line,"<") && $fromisSet == 0)
            {
                //   echo $line;
                $from = $line;
                $from = substr($from, stripos($from, ":")+2);
                if(stristr($from, "<"))
                {
                    $from = substr($from, stripos($from, "<"));
                }
                $from = str_replace(array("<", ">"," ","\n","\r"),"", $from);
                $fromisSet = 1;
            }
            if(strstr($line,"Subject: ") && $subjectisSet == 0)
            {
                //  echo $line;
                $subject = $line;
                $subjectisSet =1;
            }
            if(stristr($line,"content-type: "))
            {
                $isbody = 1;
            }


        }
        //echo "\n\n\nPARSED INFORMATION \n\n\nfrom:".$from."99\n";
        //echo "subject: ".$subject."\n";
        //echo "body: ".$body."\n";
        fclose($file);
        if(strlen($body)>5) {
            $result1 = detectOOOmessage($subject, $body, $from);
            if($result1 == 0)
            {
                detectBIOmessage($subject, $from);
            }
            if($result1 !=3)
            {
                unlink($path . "/" . $afile);
            }
            else{
                //send an email to the sys admin saying that the system got confused by this email look at it
            }
        }
    }
    $daysAway = $dbConn->query("Select setting from reassign_rules where rule_id  = 2")->fetch_assoc()["setting"];
    $daysAway = $daysAway * -1;
        $dbConn->query("DELETE FROM away_mentor WHERE tiStamp <= DATE_ADD(NOW(), INTERVAL $daysAway DAY) limit 10");//delete mentors that have been away for more than 24 hours from the away list
}
/*
 * Scans the subject and body of the email received and if it meets criteria for being a out of office message
 * passes the relevant details to SetasAway
 */
function detectOOOmessage($subjectline, $body, $email)
{
    if (stristr($subjectline, "Auto") || stristr($subjectline, "out of office")) {
        if (stristr($body, "out of office")) {
           // echo "it found an out of office message";
            $dbconnect = establishDBConnection();
            $isAwayAlready = $dbconnect->query("SELECT * FROM user  INNER JOIN away_mentor ON user.id = away_mentor.userID WHERE email LIKE '$email'");
            if ($isAwayAlready->num_rows<=0) {
              //  echo "the mentor isnt away so it should try to set them as away";
                $awayment1 = $dbconnect->query("SELECT * FROM user WHERE email LIKE '$email'");
                //$awayment = User::model()->findAllByAttributes(array('email' => $email));
                if($awayment1->num_rows>0) {
                    $awayment = $awayment1->fetch_assoc();
                    //   echo "calling the setAsAway function with " .$awayment["id"];
                    setAsAway($awayment["id"]);
                    return 1;//success
                }
                else
                {
                    return 3;//errror no one found
                }
            }
            return 0;//is
        }
    }
    return 0;
}
/*
 * Scans the subject of the email message and if it meets the criteria removes the mentor from the away list
 * $db   = dbcon
 * $sql = "select * from table";
 * $restults = $db->query($sql);
 * if($results->num_rows>0)
 * while($aresult = results->fetch_assoc())
 * {
 * $results2 = $db->query("Select * from another_table where a_collumn = " . $aresult["collumn_name"] . " rest of sql");
 * resultmodelthign
 */
function detectBIOmessage($subjectline, $email)
{
    $dbconnect = establishDBConnection();
    if (stristr($subjectline, "Back in Office")) {
        $awayment1 = $dbconnect->query("SELECT * FROM user WHERE email LIKE '$email'");
        $awayment = $awayment1->fetch_assoc();
        $dbconnect->query("DELETE FROM away_mentor WHERE userID =" . $awayment["id"] . " limit 1");

    }
}
/*
 * Using the away mentors id it adds them to the away_mentor table on the database no longer allowing them to be
 * assigned tickets.  It then finds all tickets that have been assigned to this mentor and reassigns them to compatible
 * mentors.
 * Upon each ticket assigned it sends an email to the new mentor notifying them of the new ticket, it compiles a list of
 * the tickets that have been removed from the away mentor and sends an email notifying the mentor of the removal
 *
 * And adds a new event to the tickets noting that it has been assigned to a new mentor
 */
function setAsAway($user_Id)
{
    $dbconnect = establishDBConnection();
    $dbconnect->query("INSERT INTO away_mentor (userID, tiStamp) VALUES ($user_Id, NOW())");

    $ticketSubs = "";
    $hoursForTickets = $dbconnect->query("Select setting from reassign_rules where rule_id  = 3")->fetch_assoc()["setting"];
    $hoursForTickets = $hoursForTickets * -1;
    $ftickets = $dbconnect->query("SELECT * FROM ticket WHERE assign_user_id = $user_Id AND assigned_date >= DATE_ADD(NOW() , INTERVAL $hoursForTickets HOUR )");//find tickets assigned to this user within last 24 hours
    while ($aticket = $ftickets->fetch_assoc()) {
      //  echo "a ticket is being looked at from kimora hideki";
        if (!is_null($aticket["subdomain_id"])) {
            $sql = "SELECT * FROM user_domain left join (select assign_user_id, assigned_date from (select * from ticket order by assigned_date desc)x  group by assign_user_id)x on assign_user_id = user_id WHERE domain_id = " . $aticket["domain_id"] . " AND subdomain_id = " . $aticket["subdomain_id"] . " AND tier_team = 1 AND user_id not in (select userID as user_id from away_mentor) order by assigned_date ASC   ";
            }
        else {
            $sql = "SELECT * FROM user_domain left join (select assign_user_id, assigned_date from (select * from ticket order by assigned_date desc)x  group by assign_user_id)x on assign_user_id = user_id WHERE domain_id = " . $aticket["domain_id"] . " AND tier_team = 1 AND user_id not in (select userID as user_id from away_mentor) order by assigned_date ASC  ";
            }

        $possibleMentors = $dbconnect->query($sql);
        //if there are no available mentors on the domain associated with the ticket gives the ticket to the admin for
        // manual reassignment
        if ($possibleMentors->num_rows<=0)
        {
           // echo "no possible mentors should assign tickets to admin";
            $ticketSubs = $ticketSubs . $aticket["subject"] . ",<br/>";
            $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), ".$aticket["assign_user_id"].", 5, null, 5)");
            $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = 5 WHERE id = ".$aticket["id"]);//no possible mentor found assign to admin for manual assign.
        }
        else {
            $assigned = 0;
            while ($aMentor = $possibleMentors->fetch_assoc()) {
               // echo"going through posssible mentors";
                $count1 = $dbconnect->query("SELECT COUNT(id) as `id` FROM ticket WHERE assign_user_id = " . $aMentor["user_id"]);
                $adomainMentor1 = $dbconnect->query("SELECT * FROM domain_mentor WHERE user_id = " . $aMentor["user_id"]);
                $count = $count1->fetch_assoc();
                $adomainMentor = $adomainMentor1->fetch_assoc();
                if ($adomainMentor) {
                    if ($count['id'] < $adomainMentor["max_tickets"]) {
                       // echo"this mentor can be assigned new tickets";
                        $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = " . $aMentor["user_id"] . " WHERE id = " . $aticket["id"]);
                        $mentorb1 = $dbconnect->query("SELECT * FROM user WHERE id = " . $aMentor["user_id"]);
                        $mentorb = $mentorb1->fetch_assoc();
                        sendTicketReassignment($mentorb["email"], $aticket["subject"], $aticket["id"]);
                        $assigned =1;
                        // echo"assinged new ticket to mentor";
                        $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), $user_Id, ".$aMentor["user_id"].", null, 5)");
                        break;
                    }
                } else { //not registered as having a max cket.
                 //   echo"mentor available not on assigned ticket";
                    $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = " . $aMentor["user_id"] . " WHERE id = " . $aticket["id"]);
                    $mentorb1 = $dbconnect->query("SELECT * FROM user WHERE id = " . $aMentor["user_id"]);
                    $mentorb = $mentorb1->fetch_assoc();
                    sendTicketReassignment($mentorb["email"], $aticket["subject"], $aticket["id"]);
                    $assigned=1;
                    $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), $user_Id, ".$aMentor["user_id"].", null, 5)");
                    break;
                }

            }
            if($assigned != 1)
            {
                $ticketSubs = $ticketSubs . $aticket["subject"] . ",<br/>";
                $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), $user_Id, 5, null, 5)");
                $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = 5 WHERE id = ".$aticket["id"]);//give to admin for manual reassign
            }
        }
        $ticketSubs = $ticketSubs . $aticket["subject"] . ",<br/> ";
        // do this outside the loop  $awayMent = User::model()->findAllBySql("SELECT * FROM user WHERE id =:user_Id", array(":user_id"=>$user_Id));
        // foreach ($awayMent as $bawayMent) {
        //    User::model()->sendEmailTicketCancelOutOfOffice($bawayMent->fname . " " . $bawayMent - lname, $bawayMent->email, $aticket->subject);
        //}
    }
    $mentor2 = $dbconnect->query("SELECT * FROM user WHERE id = $user_Id");
    $mentor = $mentor2->fetch_assoc();
    sendTicketCancelEmail($mentor["email"],$ticketSubs, $user_Id);

}
/*
 * Sends the list of removed emails to the mentors email address
 */
function sendTicketCancelEmail($toEmail, $subjectlines, $user_Id)
{
   // echo"\n";
   // echo $toEmail .  $subjectlines;
    $dbcon = establishDBConnection();
    $rule = $dbcon->query("Select * from reassign_rules where rule_id = 2")->fetch_assoc();
    $subject = "Out of Office Response";
    $removelink = "http://".serverName."/index.php/awayMentor/remove/".$user_Id;
    $removeClick = "<a href='$removelink'>'Click Here'</a>";
    $body = "Collaborative Platform received an Automated Out of office response from this email.<br/><br/>We have set you as out of office and you will no longer be assigned tickets automatically.<br/>The tickets : <br/><br/>" . $subjectlines . "<br/><br/>Have been reassigned to another mentor<br/><br/>If this was done in error or you are back in office send an email to fiucoplat@cp-dev.cs.fiu.edu with:<br/><br/>\"Back in office\"<br/><br/>in the subject, or ".$removeClick.", and the system will take you off of the away list or, otherwise the system will take you off of the away list automatically after ".$rule["setting"]." days<br/><br/>Thank you for all your help making Collaborative Platform great";
    $headers = 'From: Collaborative Platform <fiucoplat@cp-dev.cs.fiu.edu>' . "\r\n" .
        'Reply-To: fiucoplat@cp-dev.cs.fiu.edu' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n".
        'X-Mailer: PHP/' . phpversion();

//send the email using IMAP
    if( $a = mail($toEmail, $subject, $body, $headers))
    {  //  echo "Email sent 3!<br />";
    }
    //else{echo "didnt sent";}
}
/*
 * Sends the information of the ticket that has been reassigned to the new mentor
 */
function sendTicketReassignment($toEmail, $subjectl, $ticket_id)
{

    $dbcon = establishDBConnection();
    $ticket = $dbcon->query("Select * from ticket where id = $ticket_id")->fetch_assoc();
    $priority = $dbcon->query(("Select * from priority where id = ".$ticket["priority_id"]))->fetch_assoc();
    $subject = "Ticket Assigned";
    $linkAddress = "http://".serverName."/index.php/ticket/view/".$ticket_id;
    $rejectAddress = "http://".serverName."/index.php/ticket/reject/".$ticket_id;
    $rejectClick = "<a href='$rejectAddress'>'Click Here to reject the ticket'</a>";
    $subjectClick = "<a href='$linkAddress'>'Click Here'</a>";
    $body = "Collaborative Platform has assigned you a new ticket.<br/><br/>Subject: " . $subjectl . "<br/><br/>Description: ".$ticket["description"]."<br/><br/>that was previously assigned to another mentor. Due to the ".$priority["description"]." priority of the ticket please make a comment on or schedule a meeting with the ticket creator within ".$priority["reassignHours"]." hours<br/><br/>".$subjectClick." to view the ticket.<br/><br/> Thank you for Making Collaborative Platform Great<br/><br/>If for any reason you are unable to work on the ticket ".$rejectClick;
    $headers = 'From: Collaborative Platform <fiucoplat@cp-dev.cs.fiu.edu>' . "\r\n" .
        'Reply-To: fiucoplat@cp-dev.cs.fiu.edu' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n".
        'X-Mailer: PHP/' . phpversion();

//send the email using IMAP
    if( $a = mail($toEmail, $subject, $body, $headers))
    {  //  echo "Email sent 3!<br />";
    }
}
/*
 * Finds the tickets that have been assigned and haven't had mentor activity on them for more time then the priority
 * the ticket creator specified. The mentor activity that qualifies the ticket to not be reassigned is the mentor adding
 * a comment on the ticket or the mentor scheduling a meeting with the ticket creator.
 *
 * It adds the previous mentor to a list on the database of previous mentors so that the ticket will not be assigned to
 * a the same mentor.
 * If the ticket has been assigned three times and still has failed to have mentor activity it is then assigned to the
 * admin for manual reassignment
 *
 * It then reassigns each ticket notifying the new and old mentors through email
 */
function checkPriorityElapseTickets()
{
    $dbconnect = establishDBConnection();
    $prio = $dbconnect->query("Select * FROM priority");
    while ($prios = $prio->fetch_assoc())
    {
        switch ($prios["id"])
        {
            case 1:
                $high = $prios["reassignHours"] * -1;
                break;
            case 2:
                $med = $prios["reassignHours"] * -1;
                break;
            case 3:
                $low = $prios["reassignHours"] * -1;
                break;
        }
    }
    $ticketr = $dbconnect->query("Select * FROM ticket t where (status != 'Close' and status != 'Reject' and assign_user_id != 5 ) AND ((priority_id = 1 AND assigned_date <= DATE_ADD(NOW(), INTERVAL $high HOUR)) OR (priority_id = 2 AND assigned_date <= DATE_ADD(NOW(), INTERVAL $med HOUR)) OR (priority_id = 3 AND assigned_date <= DATE_ADD(NOW(), INTERVAL $low HOUR))) AND id NOT IN (SELECT ticket_id as id FROM ticket_events where event_type_id = 5 and event_performed_by_user_id = t.assign_user_id) AND  not exists (Select null from (video_conference inner join vc_invitation on id = videoconference_id) where t.assign_user_id = moderator_id and t.creator_user_id = invitee_id and subject like CONCAT(t.subject,' - Ticket #',t.id)) ");
    //select all tickets without a ticket event 5 or MAYBE 8 (ask juan) over their respective priorities VERY COMPLICATED SQL query
    // reassign tickets
    if($ticketr->num_rows>0) {
        while ($aticket = $ticketr->fetch_assoc()) {
            $toManyReassign = $dbconnect->query("SELECT count(ticket_id) as count from ticket_events where ticket_id = ".$aticket["id"]. " and event_type_id = 3");
            if($toManyReassign->num_rows>0)
            {
                $tomanyCount = $dbconnect->query("SELECT setting from reassign_rules where rule_id = 1")->fetch_assoc()["setting"];
                $reassigns = $toManyReassign->fetch_assoc();
                if($reassigns["count"] >= $tomanyCount)
                {
                    $mentor = $dbconnect->query("Select * from user WHERE id = ".$aticket["assign_user_id"]);
                    $aMentor = $mentor->fetch_assoc();
                    sendTicketCancelOutOfTime($aMentor["email"], $aticket["subject"]);
                    $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), ".$aticket["assign_user_id"].", 5, null, 5)");
                    $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = 5 WHERE id = ".$aticket["id"]);//give to admin for manual reassign

                    continue;
                }
            }
            $mentor = $dbconnect->query("Select * from user WHERE id = ".$aticket["assign_user_id"]);
            $aMentor = $mentor->fetch_assoc();
            sendTicketCancelOutOfTime($aMentor["email"], $aticket["subject"]);
            //$dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), ".$aticket["assign_user_id"].",null, null, 5)");
            $oldMentor = $aticket["assign_user_id"];
            if (!is_null($aticket["subdomain_id"])) {
                $sql = "SELECT * FROM user_domain left join (select assign_user_id, assigned_date from (select * from ticket order by assigned_date desc)x  group by assign_user_id)x on assign_user_id = user_id WHERE domain_id = " . $aticket["domain_id"] . " AND subdomain_id = " . $aticket["subdomain_id"] . " AND tier_team = 1 AND user_id not in (select userID as user_id from away_mentor) and user_id != $oldMentor AND user_id not in (select old_value as user_id from ticket_events where ticket_id = ". $aticket["id"]." and event_type_id = 3) order by assigned_date ASC   ";
                }
            else {
                $sql = "SELECT * FROM user_domain left join (select assign_user_id, assigned_date from (select * from ticket order by assigned_date desc)x  group by assign_user_id)x on assign_user_id = user_id  WHERE domain_id = " . $aticket["domain_id"] . " AND tier_team = 1 AND user_id not in (select userID as user_id from away_mentor) AND user_id not in (select * from ticket order by assigned_date desc)x  group by assign_user_id)x on assign_user_id = user_id WHERE domain_id = " . $aticket["domain_id"] . " AND subdomain_id = " . $aticket["subdomain_id"] . " AND tier_team = 1 AND user_id not in (select userID as user_id from away_mentor)and user_id != $oldMentor AND user_id not in (select old_value as user_id from ticket_events where ticket_id = ". $aticket["id"]." and event_type_id = 3) order by assigned_date ASC   ";
            }
            $possibleMentors = $dbconnect->query($sql);
            if ($possibleMentors->num_rows<=0)
            {
                $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = 5 WHERE id = ".$aticket["id"]);//give to admin for manual reassign
            }
            else {
                $assinged = 0;
                while ($aMentor = $possibleMentors->fetch_assoc()) {
                    $count1 = $dbconnect->query("SELECT COUNT(id) as `id` FROM ticket WHERE assign_user_id = " . $aMentor["user_id"]);
                    $adomainMentor1 = $dbconnect->query("SELECT * FROM domain_mentor WHERE user_id = " . $aMentor["user_id"]);
                    $count = $count1->fetch_assoc();
                    $adomainMentor = $adomainMentor1->fetch_assoc();
                    if ($adomainMentor) {
                        if ($count['id'] < $adomainMentor["max_tickets"]) {
                            $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = " . $aMentor["user_id"] . " WHERE id = " . $aticket["id"]);
                            $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), $oldMentor, ".$aMentor["user_id"].", null, 5)");
                            $mentorb1 = $dbconnect->query("SELECT * FROM user WHERE id = " . $aMentor["user_id"]);
                            $mentorb = $mentorb1->fetch_assoc();
                            sendTicketReassignment($mentorb["email"], $aticket["subject"], $aticket["id"]);
                            $assinged = 1;
                            break;
                        }
                    } else { //not registered as having a max ticket.
                        $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = " . $aMentor["user_id"] . " WHERE id = " . $aticket["id"]);
                        $dbconnect->query("insert into ticket_events (event_type_id, ticket_id, event_recorded_date, old_value, new_value, comment, event_performed_by_user_id) values (3, ".$aticket["id"].", NOW(), $oldMentor, ".$aMentor["user_id"].", null, 5)");
                        $mentorb1 = $dbconnect->query("SELECT * FROM user WHERE id = " . $aMentor["user_id"]);
                        $mentorb = $mentorb1->fetch_assoc();
                        sendTicketReassignment($mentorb["email"], $aticket["subject"], $aticket["id"]);
                        $assinged = 1;
                        break;
                    }
                }
                if($assinged != 1)
                {
                   $dbconnect->query("UPDATE ticket SET assigned_date = NOW(), assign_user_id = 5 WHERE id = ".$aticket["id"]);//give to admin for manual reassign
                }

            }
          //  echo "a went through entire thing ticket should be reassigned\n";
        }
    }
}
/*
 * Sends an email to the mentor notifying them that a ticket they were assigned was reassigned to a new mentor because
 * the ticket ran out of time before there was qualifying activity on the ticket.
 */
function sendTicketCancelOutOfTime($toEmail, $subjectLine)
{
    $subject = "Reassign Due to Inactivity";
    $body = "Due to the inactivity on the ticket:<br/><br/>$subjectLine <br/><br/>has been reassigned.<br/><br/>Thank you for all your help making Collaborative Platform great";
    $headers = 'From: Collaborative Platform <fiucoplat@cp-dev.cs.fiu.edu>' . "\r\n" .
        'Reply-To: fiucoplat@cp-dev.cs.fiu.edu' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n".
        'X-Mailer: PHP/' . phpversion();

//send the email using IMAP
    if( $a = mail($toEmail, $subject, $body, $headers))
    {  //  echo "Email sent 3!<br />";
    }
}

emailListener();
checkPriorityElapseTickets();
?>

