<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 6/11/2015
 * Time: 1:44 PM
 */
function establishDBConnection()
{
    $username = 'root';
    $password = '9Qst32+';
    $dbconn = new mysqli("localhost", $username, $password, "coplat");
    return $dbconn;
}
function sendTicketReassignment()
{
$dbConnect = establishDBConnection();
    $query = $dbConnect->query("SELECT * from ticket where id = 87")->fetch_assoc();
    $ticket_id = $query["id"];
    $subjectl = $query["subject"];
    $toEmail = "mmach059@fiu.edu";
    $link = "http://cp-dev.cis.fiu.edu/coplat/index.php/ticket/view/".$ticket_id;
    $subject = "Ticket Assigned";
    $subjectClick = "<a href='". $link. "'>" .$subjectl. "</a>\n\n";
    $body = "Collaborative Platform has assigned you a new ticket:\n\n" . $subjectClick . "\n\nthat was previously assigned to another mentor.\n Thank you for Making Collaborative Platform Great";
    $headers = 'From: Collaborative Platform <fiucoplat@cp-dev.cs.fiu.edu>' . "\r\n" .
        'Content-type: text/html; charset=utf-8' . "\r\n".
        'Reply-To: fiucoplat@cp-dev.cs.fiu.edu' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    echo "subject click ".$subjectClick."\n";
    echo "link ".$link."\n";
//send the email using IMAP
    if( $a = mail($toEmail, $subject, $body, $headers))
    {  echo "Email sent 3!<br />";
    }
    else{echo "email not sent\n";}
}
//sendTicketReassignment();
//echo "find";
phpinfo();