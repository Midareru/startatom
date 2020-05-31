<?php
function auth_stat($login,$pass){//{authorized:boolean,error:boolean,errortext:string}
    global $config;
    if ($login===$config["statlogin"] && $pass===$config["statpass"]){
        global $db;
        $key=sha1($config["statlogin"]).random_int(10000,99999);
        setcookie("atomkeystat",$key,time()+1123200,"/");
        $ip=$_SERVER['REMOTE_ADDR'];
        $id=$config["statid"];
        $sql="INSERT INTO `session`(`session_key`, `log_date`,`user`,`ip`) VALUES ('$key',CURRENT_TIMESTAMP,$id,'$ip')";
        $db->query($sql);
        //Очистка старых записей сессии при входе любого пользователя. Мало ли.
        $db->query("DELETE FROM `session` WHERE DATEDIFF(CURRENT_TIMESTAMP, `log_date`)>14");
    }
    return array("authorized"=>true,"error"=>false,"errortext"=>"");
}
function check_auth(){//{authorized:boolean,error:boolean,errortext:string}
    $output=array("authorized"=>false,"error"=>false,"errortext"=>"");
    if (isset($_COOKIE["atomkeystat"])){
        global $db;
        global $config;
        $stmt = $db->prepare('SELECT `user` FROM `session` WHERE `session_key`=?');
        $stmt->bind_param('s', $_COOKIE["atomkeystat"]);
        $stmt->execute();
        $res=$stmt->get_result();
        if ($res->num_rows==1)
        {
            $row=$res->fetch_array();
            $id=$row['user'];
            $stmt->close();
            if ($id==$config["statid"])
            $output=array("authorized"=>true,"error"=>false,"errortext"=>"");//Пусть пока так.
        }
    }
    return $output;
}
function logout(){//{authorized:boolean,error:boolean,errortext:string}
    global $db;
    $stmt=$db->prepare("DELETE FROM `session` WHERE `session_key`= ?");
    $stmt->bind_param("s",$_COOKIE["atomkeystat"]);
    $stmt->execute();
    $stmt->close();
    setcookie("atomkeystat","",time()-1123200,"/");
    return check_auth();
}
function get_stat_exp($start_date,$end_date,$expertise){//{stat:{mark:{number:int,amount:int},time:{deviation:int,amount:int}},error:boolean,errortext:string}
    if ($start_date=="") $start_date="2000-01-01";
    if ($end_date=="") $end_date="9999-12-31";
    $marks=get_marks_exp($start_date,$end_date,$expertise);
    $time=get_time_exp($start_date,$end_date,$expertise);
    return ["stat"=>["marks"=>$marks,"time"=>$time],"error"=>false,"errortext"=>""];
}
function get_stat_spec($start_date,$end_date,$specialist){//{stat:{mark:{number:int,amount:int},time:{deviation:int,amount:int}},error:boolean,errortext:string}
    if ($start_date=="") $start_date="2000-01-01";
    if ($end_date=="") $end_date="9999-12-31";
    $marks=get_marks_spec($start_date,$end_date,$specialist);
    $time=get_time_spec($start_date,$end_date,$specialist);
    return ["stat"=>["marks"=>$marks,"time"=>$time],"error"=>false,"errortext"=>""];
}
function get_marks_spec($start_date,$end_date,$specialist){
    global $db;
    $arr=json_decode($specialist);
    $arr=array_map('intval',$arr);
    $arr = implode("','",$arr);
    $sql="SELECT specialists.name, avg(route.mark) FROM route LEFT JOIN spec_expertise ON route.expertise=spec_expertise.id LEFT JOIN specialists ON specialists.id=spec_expertise.specialist WHERE (start_time BETWEEN ? AND ?)";
    $sql=$sql." AND spec_expertise.specialist IN ('".$arr."') ";
    $sql=$sql."  GROUP BY spec_expertise.specialist ORDER BY specialists.name ASC";
    $stmt=$db->prepare($sql);
    $stmt->bind_param('ss', $start_date,$end_date);
    $stmt->execute();
    $res=$stmt->get_result();
    $marks=[];
    while ($row=$res->fetch_row()){
        array_push($marks,["name"=>$row[0],"mark"=>$row[1]]);
    }
    return $marks;
}
function get_marks_exp($start_date,$end_date,$expertise){
    global $db;
    $arr=json_decode($expertise);
    $arr=array_map('intval',$arr);
    $arr = implode("','",$arr);
    $sql="SELECT expertises.name, avg(route.mark) FROM route LEFT JOIN spec_expertise ON route.expertise=spec_expertise.id LEFT JOIN expertises ON expertises.id=spec_expertise.expertise WHERE (start_time BETWEEN ? AND ?)";
    $sql=$sql." AND spec_expertise.expertise IN ('".$arr."') ";
    $sql=$sql."  GROUP BY spec_expertise.expertise ORDER BY expertises.name ASC";
    $stmt=$db->prepare($sql);
    $stmt->bind_param('ss', $start_date,$end_date);
    $stmt->execute();
    $res=$stmt->get_result();
    $marks=[];
    while ($row=$res->fetch_row()){
        array_push($marks,["name"=>$row[0],"mark"=>$row[1]]);
    }
    return $marks;
}
function get_time_spec($start_date,$end_date,$specialist){
    global $db;
    $arr=json_decode($specialist);
    $arr=array_map('intval',$arr);
    $arr = implode("','",$arr);
    $sql="SELECT specialists.name, avg(expertises.time-route.real_time) FROM route LEFT JOIN spec_expertise ON route.expertise=spec_expertise.id LEFT JOIN expertises ON expertises.id=spec_expertise.expertise LEFT JOIN specialists ON specialists.id=spec_expertise.specialist WHERE (start_time BETWEEN ? AND ?)";
    $sql=$sql." AND spec_expertise.specialist IN ('".$arr."') ";
    $sql=$sql." GROUP BY specialists.id ORDER BY specialists.name ASC";
    $stmt=$db->prepare($sql);
    $stmt->bind_param('ss', $start_date,$end_date);
    $stmt->execute();
    $res=$stmt->get_result();
    $marks=[];
    while ($row=$res->fetch_row()){
        array_push($marks,["name"=>$row[0],"dev"=>$row[1]]);
    }
    return $marks;
}
function get_time_exp($start_date,$end_date,$expertise){
    global $db;
    $arr=json_decode($expertise);
    $arr=array_map('intval',$arr);
    $arr = implode("','",$arr);
    $sql="SELECT expertises.name, avg(expertises.time-route.real_time) FROM route LEFT JOIN spec_expertise ON route.expertise=spec_expertise.id LEFT JOIN expertises ON expertises.id=spec_expertise.expertise WHERE (start_time BETWEEN ? AND ?)";
    $sql=$sql." AND spec_expertise.expertise IN ('".$arr."') ";
    $sql=$sql." GROUP BY expertises.id ORDER BY expertises.name ASC";
    $stmt=$db->prepare($sql);
    $stmt->bind_param('ss', $start_date,$end_date);
    $stmt->execute();
    $res=$stmt->get_result();
    $marks=[];
    while ($row=$res->fetch_row()){
        array_push($marks,["name"=>$row[0],"dev"=>$row[1]]);
    }
    return $marks;
}
function get_specs(){
    global $db;
    $res=$db->query("SELECT `id`,`name` FROM `specialists` ORDER BY `name`");
    $arr=[];
    while ($row=$res->fetch_assoc()){
        array_push($arr,$row);
    }
    return ["specs"=>$arr,"error"=>false,"errortext"=>""];
}
function get_exps(){
    global $db;
    $res=$db->query("SELECT `id`,`name` FROM `expertises` ORDER BY `name`");
    $arr=[];
    while ($row=$res->fetch_assoc()){
        array_push($arr,$row);
    }
    return ["specs"=>$arr,"error"=>false,"errortext"=>""];
}
if(isset($_POST["action"])){
    include_once("config.php");
    $auth=check_auth();
    if ($auth["authorized"]){
        if (strpos($_POST["action"],"auth")===0){
            exit(json_encode(array("error"=>true,"errortext"=>"Уже авторизован")));
        }
    }
    else{
        if (strpos($_POST["action"],"auth")<0){
            exit(json_encode(array("error"=>true,"errortext"=>"Не авторизован")));
        }
    }
    switch ($_POST["action"]){
        case "check_auth":
            echo json_encode($auth);
            break;
        case "auth_stat":
            echo json_encode(auth_stat($_POST["login"],$_POST["pass"]));
            break;
        case "logout":
            echo json_encode(logout());
            break;
        case "get_specs":
            echo json_encode(get_specs());
            break;
        case "get_exps":
            echo json_encode(get_exps());
            break;
        case "get_stat_spec":
            echo json_encode(get_stat_spec($_POST["start"],$_POST["end"],$_POST["spec"]));
            break;
        case "get_stat_exp":
            echo json_encode(get_stat_exp($_POST["start"],$_POST["end"],$_POST["exp"]));
            break;
    }
}