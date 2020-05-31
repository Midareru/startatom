<?php
include_once("config.php");
function admin_auth($login,$pass){//{authorized:boolean,error:boolean,errortext:string}

}
function is_admin(){//{authorized:boolean,error:boolean,errortext:string}

}
function get_specialists(){//{specialists:{id:int,name:string,cabinet:string,expertises:{id:int,name:string},timetable:{id:int,day:int,start:time,end:time,break_start:time,break_end:time}},error:boolean,errortext:string}

}
function specialist_remove($id){//{error:boolean,errortext:string}

}
function specialist_add($name,$cabinet){//{id:int,error:boolean,errortext:string}

}
function specialist_change($id,$name,$cabinet,$expertises,$timetable){//{error:boolean,errortext:string}

}

function get_exams(){//{exams:{id:int,name:string,p_categories{id:int,name:string},expertises:{id:int,name:string}},error:boolean,errortext:string}

}
function exam_add($name,$categories){//{id:int,error:boolean,errortext:string}

}
function exam_remove($id){//{error:boolean,errortext:string}

}

function get_cabinets(){//{cabinets:{id:int,name:string,timeto:{id:int,cabinet:int,time:int}},error:boolean,errortext:string}

}
function cabinet_add($name){//{id:int,error:boolean,errortext:string}

}
function cabinet_change($id,$name,$timetable){//{error:boolean,errortext:string}

}
function cabinet_remove($id){//{error:boolean,errortext:string}

}
function get_expertises(){//{expertises:{id:int,name:string,time:int,depends:{id:int}},error:boolean,errortext:string}

}
function expertise_add($name,$time){//{id:int,error:boolean,errortext:string}

}
function expertise_change($id,$name,$time,$dependents){//{error:boolean,errortext:string}

}
function expertise_remove($id){//{error:boolean,errortext:string}

}
function create_routs($xml){//{error:boolean,errortext:string}

}
