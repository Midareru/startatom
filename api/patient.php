<?php
//ŒÍÂÈ, ‚ÂÏÂÌË Ï‡ÎÓ, ÒÎË¯ÍÓÏ Ï‡ÎÓ, ÔÓ˝ÚÓÏÛ ·ÂÁ Í‡ÒË‚ÓÒÚÂÈ, ˜ÂÎÓ‚ÂÍÓÔÓÌˇÚÌ˚ı ÍÓÏÏÂÌÚ‡ËÂ‚, ÔÓ‚ÂÓÍ Ë ÔÂ‰ÓÒÚÓÓÊÌÓÒÚÂÈ. ÕÓ Í‡Í Á‡„ÓÚÓ‚Í‡ ÔÓÈ‰∏Ú.
function check_auth(){//{authorized:boolean,error:boolean,errortext:string}
    $output=array("authorized"=>false,"error"=>false,"errortext"=>"");
    if (isset($_COOKIE["atomkey"])){
        global $db;
        $stmt = $db->prepare('SELECT `user` FROM `session` WHERE `session_key`=?');
        $stmt->bind_param('s', $_COOKIE["atomkey"]);
        $stmt->execute();
        $res=$stmt->get_result();
        if ($res->num_rows==1)
        {
            $row=$res->fetch_array();
            $id=$row['user'];
            $stmt->close();
            $output=array("authorized"=>true,"id"=>$id,"error"=>false,"errortext"=>"");
        }
    }
    return $output;
}
function auth_start($number){//{continue:boolean,error:boolean,errortext:string}
    global $db;
    $stmt = $db->prepare('SELECT `id` FROM `patients` WHERE `number`=?');
    $stmt->bind_param('s', $number);
    $stmt->execute();
    $res=$stmt->get_result();
    if ($res->num_rows==1)
    {
        setcookie("atomnumber",$number,time()+60000,"/");
        $row=$res->fetch_array();
        $stmt->close();
        $id=$row['id'];
        $code=rand(100000,999999);
        $sendres=send($number,$code);
        if (!$sendres){
            $db->query("REPLACE INTO `login`(`user`,`code`) VALUES('$id','$code')");
            $output=array("continue"=>true,"error"=>false,"errortext"=>"");
        }
        else $output=array("continue"=>false,"error"=>true,"errortext"=>"–û—à–∏–±–∫–∞ ‚Ññ$sendres –ø—Ä–∏ –æ—Ç–ø—Ä–∞–≤–∫–µ —Å–º—Å ");
    }
    else
    {
        $output=array("continue"=>false,"error"=>true,"errortext"=>"–ü–æ–ª—å–∑–æ–≤–∞—Ç–µ–ª—è –Ω–µ —Å—É—â–µ—Å—Ç–≤—É–µ—Ç");
    }
    return json_encode($output);
}
function send($number,$code){
    include_once("smsc_api.php");
    $result=send_sms($number,"–í–∞—à –∫–æ–¥ –¥–ª—è –≤—Ö–æ–¥–∞: $code");
    if (count($result)>2){
        return false;
    }
    else return -$result[1];
}
function auth_end($number,$code){//{authorized:boolean,error:boolean,errortext:string}
    setcookie("atomnumber",$number,time()-60000,"/");
    unset($_COOKIE["atomnumber"]);
    global $db;
    $stmt = $db->prepare('SELECT `user` FROM `login` JOIN `patients` ON `user`=`id` WHERE `number`=? AND `code`=?');
    $stmt->bind_param('ss', $number,$code);
    $stmt->execute();
    $res=$stmt->get_result();
    if ($res->num_rows==1)
    {
        $row=$res->fetch_array();
        $stmt->close();
        $id=$row['user'];
        $key=sha1($number).random_int(10000,99999);
        setcookie("atomkey",$key,time()+1123200,"/");
        $ip=$_SERVER['REMOTE_ADDR'];
        $sql="INSERT INTO `session`(`session_key`, `log_date`,`user`,`ip`) VALUES ('$key',CURRENT_TIMESTAMP,$id,'$ip')";
        $db->query($sql);
        //–û—á–∏—Å—Ç–∫–∞ —Å—Ç–∞—Ä—ã—Ö –∑–∞–ø–∏—Å–µ–π —Å–µ—Å—Å–∏–∏ –ø—Ä–∏ –≤—Ö–æ–¥–µ –ª—é–±–æ–≥–æ –ø–æ–ª—å–∑–æ–≤–∞—Ç–µ–ª—è. –ú–∞–ª–æ –ª–∏.
        $db->query("DELETE FROM `session` WHERE DATEDIFF(CURRENT_TIMESTAMP, `log_date`)>14");
        $output=array("authorized"=>true,"error"=>false,"errortext"=>"");
    }
    else
    {
        $output=array("authorized"=>false,"error"=>true,"errortext"=>"–ù–µ–ø—Ä–∞–≤–∏–ª—å–Ω—ã–π –∫–æ–¥ –∏–ª–∏ –∏—Å—Ç—ë–∫ —Å—Ä–æ–∫ –¥–µ–π—Å—Ç–≤–∏—è");
    }
    return json_encode($output);
}
function logout(){//{authorized:boolean,error:boolean,errortext:string}
    global $db;
    $stmt=$db->prepare("DELETE FROM `session` WHERE `session_key`= ?");
    $stmt->bind_param("s",$_COOKIE["atomkey"]);
    $stmt->execute();
    $stmt->close();
    setcookie("atomkey","",time()-1123200,"/");
    return check_auth();
}
function get_exams($userid){//{exams:{id:int,type:string,start:date,end:date,status:int,status:string},error:boolean,errortext:string}
    global $db;
    $res=$db->query("SELECT `set_exams`.`id`,`e_cat_list`.`name` as ename,`start_date`,`end_date`,`exam_status`.`name` as status FROM `set_exams` LEFT JOIN `exam_status` ON `exam_status`.`id`=`set_exams`.`status` LEFT JOIN `e_cat_list` ON `e_cat_list`.`id`=`set_exams`.`exam_cat` WHERE `patient_id`=$userid");
    $exams=[];
    while ($row=$res->fetch_assoc()){
        array_push($exams,$row);
    }
    return ["exams"=>$exams,"error"=>false,"errortext"=>""];
}
function get_exam($userid,$id){//{routes:{id:int,expertise:name,specialist:string,comment:string,start_time:datetime,mark:int,real_start_time:time,real_time:int},error:boolean,errortext:string}
    global $db;
    $id=intval($id);
    $res=$db->query("SELECT `route`.`id`,`expertises`.name as expertise, specialists.name as specialist, cabinets.name as cabinet, expertises.comment, route.start_time, mark, real_start_time, real_time FROM `route` LEFT JOIN `spec_expertise` ON spec_expertise.id=route.expertise LEFT JOIN specialists ON specialists.id=spec_expertise.specialist LEFT JOIN expertises ON expertises.id=spec_expertise.expertise LEFT JOIN set_exams ON set_exams.id=route.exam LEFT JOIN cabinets ON specialists.cabinet=cabinets.id WHERE route.exam=$id AND set_exams.patient_id=$userid ORDER BY `start_date` ASC");
    $routes=[];
    while ($row=$res->fetch_assoc()){
        array_push($routes,$row);
    }
    $res=$db->query("SELECT status,comment FROM set_exams WHERE `id`='$id' AND `patient_id`=$userid");
    $row=$res->fetch_row();

    return ["routes"=>$routes,"status"=>$row[0],"comment"=>$row[1],"error"=>false,"errortext"=>""];
}
function accept_exam($userid,$id){//{error:boolean,errortext:string}
    global $db;
    $id=intval($id);
    $db->query("UPDATE `set_exams` SET `status`=2 WHERE `patient_id`=$userid AND `id`=$id");
    return ["error"=>false,"errortext"=>""];
}
function delay_exam($userid,$id,$reason){//{error:boolean,errortext:string}
    global $db;
    $id=intval($id);
    $stmt = $db->prepare("UPDATE set_exams SET `status`=3, comment=? WHERE `patient_id`=$userid AND `id`=$id");
    $stmt->bind_param('s', $reason);
    $stmt->execute();
    return ["error"=>false,"errortext"=>""];
}
function end_exam($userid,$id,$comment,$routes){//{error:boolean,errortext:string}
    global $db;
    $stmt = $db->prepare("UPDATE `set_exams` SET `comment`=?, `status`=4 WHERE `patient_id`=$userid AND `id`=?");
    $stmt->bind_param('si', $comment,intval($id));
    $stmt->execute();
    if ($stmt->sqlstate==0){
        $stmt->close();
        $routes=json_decode($routes);
        $stmt=$db->prepare("UPDATE `route` SET `mark`=?,`real_start_time`=?,`real_time`=? WHERE `id`=? AND `exam`=?");
        foreach ($routes as $route){
            $stmt->bind_param('isiii', intval($route->mark),$route->start,intval($route->time),intval($route->id),intval($id));
            $stmt->execute();
        }
        $stmt->close();
        return(json_encode(array("error"=>false,"errortext"=>"")));
    }
    return(json_encode(array("error"=>true,"errortext"=>"–ß—Ç–æ-—Ç–æ –ø–æ—à–ª–æ –Ω–µ —Ç–∞–∫")));
}

if(isset($_POST["action"])){
    include_once("config.php");
    $auth=check_auth();
    if ($auth["authorized"]){
        $userid=$auth["id"];
        if (strpos($_POST["action"],"auth")===0){
            exit(json_encode(array("error"=>true,"errortext"=>"–£–∂–µ –∞–≤—Ç–æ—Ä–∏–∑–æ–≤–∞–Ω")));
        }
    }
    else{
        if (strpos($_POST["action"],"auth")<0){
            exit(json_encode(array("error"=>true,"errortext"=>"–ù–µ –∞–≤—Ç–æ—Ä–∏–∑–æ–≤–∞–Ω")));
        }
    }
    switch ($_POST["action"]){
        case "check_auth":
            echo json_encode($auth);
            break;
        case "auth_start":
            echo auth_start($_POST["number"]);
            break;
        case "auth_end":
            if (!isset($_COOKIE["atomnumber"])) {echo json_encode(array("continue"=>false,"error"=>true,"errortext"=>"–ò—Å—Ç—ë–∫ —Å—Ä–æ–∫ –¥–µ–π—Å—Ç–≤–∏—è –ø–∞—Ä–æ–ª—è –∏–ª–∏ –∑–∞–ø—Ä–µ—â–µ–Ω—ã cookies")); break;}
            echo auth_end($_COOKIE["atomnumber"],$_POST["code"]);
            break;
        case "logout":
            echo json_encode(logout());
            break;
        case "get_exams":
            echo json_encode(get_exams($userid));
            break;
        case "get_exam":
            echo json_encode(get_exam($userid,$_POST["id"]));
            break;
        case "accept_exam":
            echo json_encode(accept_exam($userid,$_POST["id"]));
            break;
        case "delay_exam":
            echo json_encode(delay_exam($userid,$_POST["id"],$_POST["reason"]));
            break;
        case "end_exam":
            echo end_exam($userid,$_POST["id"],$_POST["comment"],$_POST["routes"]);
            break;
    }
}