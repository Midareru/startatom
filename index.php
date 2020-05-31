<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script type="text/javascript" src="js/jquery.js"></script>
</head>
<body>
<div>
    <input type="text" id="number" placeholder="89000000000">
    <input type="button" value="войти" id="click">
</div>
<div>
    <input type="text" id="code" placeholder="xxxxxx">
    <input type="button" value="войти2" id="click2">
</div>
<div id="auth">

</div>
<div>
    <input type="button" value="exit" id="exit">
</div>
<div id="exams">

</div>
<div id="exam">

</div>

<script>

    $("#click").click(function () {
        let number=$("#number").val();
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"auth_start",
                number:number
            },
            type: 'POST',
            success: function (res) {
               console.log(res);
            }
        });
    });
    $("#click2").click(function () {
        let number=$("#code").val();
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"auth_end",
                code:number
            },
            type: 'POST',
            success: function (res) {
                location.reload();
            }
        });
    });
    $("#exit").click(function () {
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"logout"
            },
            type: 'POST',
            success: function (res) {
                location.reload();
            }
        });
    });


    $("#exams").on("click","tr:not(:only-child)",function () {
       let id=$(this).attr("data-id");
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"get_exam",
                id:id
            },
            type: 'POST',
            success: function (res) {
                res=JSON.parse(res);
                let status=res['status'];
                let comment=res['comment'];
                res=res["routes"];
                console.log(res);
                let table="<table><thead><tr><th>Дата и время</th><th>Кабинет</th><th>Обследование</th><th>Специалист</th><th>Комментарий</th><th>Начало приёма</th><th>Время приёма</th><th>Оценка</th></tr></thead><tbody>";
                for (let i=0;i<res.length;i++){
                    let row=res[i];
                    if (row["mark"]==null) row["mark"]=0;
                    if (row["real_start_time"]==null) row["real_start_time"]="";
                    if (row["real_time"]==null) row["real_time"]="";
                    table=table+"<tr class='route' data-id='"+row['id']+"'><td>"+row['start_time']+"</td><td>"+row['cabinet']+"</td><td>"+row['expertise']+"</td><td>"+row['specialist']+"</td><td>"+row['comment']+"</td>";
                    if (status==2)
                        table=table+"<td><input type='text' class='start' value='"+row["real_start_time"]+"'></td><td><input type='text' class='time' value='"+row["real_time"]+"'></td><td><input type='number' class='mark' value='"+row["mark"]+"'></td></tr>";
                    else
                        table=table+"<td>"+row["real_start_time"]+"</td><td>"+row["real_time"]+"</td><td>"+row["mark"]+"</td></tr>";
                }
                table=table+"</tbody></table>";
                if (status==2)table=table+"<input type='text' id='examcomment'> <input type='button' value='Пройден' id='end_exam' data-id='"+id+"'>";
                if (status==1)table=table+"<input type='text' id='examcomment'> <input type='button' value='Отложить' id='delay_exam' data-id='"+id+"'> <input type='button' value='Принять' id='accept_exam' data-id='"+id+"'>";
                $("#exam").html(table);
            }
        });
    });
    $("#exam").on("click","#end_exam",function () {
       let exam_id=$(this).attr("data-id");
       let comment=$("#examcomment").val();
       let routes=$(".route");
       let arr=[];
       routes.each(function () {
          let route=$(this);
          let id=route.attr('data-id');
          let start=route.find(".start")[0].value;
          let time=route.find(".time")[0].value;
          let mark=route.find(".mark")[0].value;
          arr.push({"id":id,"start":start,"time":time,"mark":mark});
       });
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"end_exam",
                id:exam_id,
                comment:comment,
                routes:JSON.stringify(arr)
            },
            type: 'POST',
            success: function (res) {
                location.reload();
            }
        });
    });
    $.ajax({
        url: 'api/patient.php',
        data: {
            action:"check_auth"
        },
        type: 'POST',
        success: function (res) {
            res=JSON.parse(res);
            $("#auth").html(res['authorized']+", "+res['id']);
            if (res.authorized){
                $.ajax({
                    url: 'api/patient.php',
                    data: {
                        action:"get_exams"
                    },
                    type: 'POST',
                    success: function (res) {
                        res=JSON.parse(res);
                        res=res["exams"];
                        let table="<table><thead><tr><th>Наименование</th><th>Дата начала</th><th>Дата окончания</th><th>Статус</th></tr></thead><tbody>";
                        for (let i=0;i<res.length;i++){
                            let row=res[i];
                            table=table+"<tr data-id='"+row['id']+"'><td>"+row['ename']+"</td><td>"+row['start_date']+"</td><td>"+row['end_date']+"</td><td>"+row['status']+"</td></tr>";
                        }
                        table=table+"</tbody></table>";
                        $("#exams").html(table);
                    }
                });
            }

        }
    });
    $("#exam").on("click","#accept_exam",function () {
        let exam_id=$(this).attr("data-id");
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"accept_exam",
                id:exam_id
            },
            type: 'POST',
            success: function (res) {
                location.reload();
            }
        });
    });
    $("#exam").on("click","#delay_exam",function () {
        let exam_id=$(this).attr("data-id");
        let comment=$("#examcomment").val();
        $.ajax({
            url: 'api/patient.php',
            data: {
                action:"delay_exam",
                id:exam_id,
                reason:comment
            },
            type: 'POST',
            success: function (res) {
                location.reload();
            }
        });
    });
</script>
</body>
</html>