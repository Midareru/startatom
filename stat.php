<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script type="text/javascript" src="js/jquery.js"></script>
</head>
<body>
<div>
    <input type="text" id="login" placeholder="login">
    <input type="password" id="pass" placeholder="pass">
    <input type="button" value="войти" id="click">
</div>
<div style="display: flex;flex-direction: column">
    <span>
        <input type="date" id="startdate">
        <input type="date" id="enddate">
    </span>
    <span>
        <select id="specfilt" multiple></select>
        <select id="expfilt" multiple></select>
    </span>
    <div>
        <input style="width: 100px" type="button" value="Специалист" id="graph1">
        <input style="width: 100px" type="button" value="Обследование" id="graph2">
    </div>
    <div>
        <canvas id="timegraph">

        </canvas>
        <canvas id="markgraph">

        </canvas>
    </div>
</div>
<script>
    let markgraph=new Chart($("#markgraph")[0].getContext('2d'));
    let timegraph=new Chart($("#timegraph")[0].getContext('2d'));
    $("#click").click(function () {
        $.ajax({
            url: 'api/stat.php',
            data: {
                action:"auth_stat",
                login:$("#login").val(),
                pass:$("#pass").val()
            },
            type: 'POST',
            success: function (res) {
                console.log(res);
            }
        });
    });
    $.ajax({
        url: 'api/stat.php',
        data: {
            action:"get_specs"
        },
        type: 'POST',
        success: function (res) {
            res=JSON.parse(res);
            res=res.specs;
            let select="";
            for (let i=0;i<res.length;i++){
                let spec=res[i];
                select=select+"<option value='"+spec["id"]+"'>"+spec["name"]+"</option>";
            }
            $("#specfilt").html(select);
        }
    });
    $.ajax({
        url: 'api/stat.php',
        data: {
            action:"get_exps"
        },
        type: 'POST',
        success: function (res) {
            res=JSON.parse(res);
            res=res.specs;
            let select="";
            for (let i=0;i<res.length;i++){
                let spec=res[i];
                select=select+"<option value='"+spec["id"]+"'>"+spec["name"]+"</option>";
            }
            $("#expfilt").html(select);
        }
    });
    $("#graph1").click(function () {
        let start=$("#startdate").val();
        let end=$("#enddate").val();
        let spec=$("#specfilt").val();

        $.ajax({
            url: 'api/stat.php',
            data: {
                action:"get_stat_spec",
                start:start,
                end:end,
                spec:JSON.stringify(spec)
            },
            type: 'POST',
            error: function(res){
                console.log(res);
            },
            success: function (res) {
                res=JSON.parse(res);
                let marks = res.stat.marks;
                let time = res.stat.time;
                let markcan = document.getElementById('markgraph');
                let timecan = document.getElementById('timegraph');
                markgraph.destroy();
                timegraph.destroy();
                timecan.getContext('2d').clearRect(0,0,timecan.width,timecan.height);
                let marklabels=[];
                let timelabels=[];
                let markdata=[];
                let timedata=[];
                for (let i = 0; i < marks.length; i++) {
                    markdata.push(marks[i]['mark']);
                    marklabels.push(marks[i]['name']);
                }
                for (let i = 0; i < marks.length; i++) {
                    timedata.push(time[i]['dev']);
                    timelabels.push(time[i]['name']);
                }
                const colours = timedata.map((value) => value < 0 ? 'red' : 'green');
                markgraph = new Chart(markcan.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: marklabels,
                        datasets: [{
                            label: 'Оценка',
                            data: markdata,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks:{
                                    min:0,
                                    max:6
                                }
                            }]
                        }
                    }
                });
                timegraph = new Chart(timecan.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: timelabels,
                        datasets: [{
                            label: 'Среднее отклонение',
                            data: timedata,
                            backgroundColor: colours,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks:{
                                    min:Math.min(...timedata)-1,
                                    max:Math.max(...timedata)+1
                                }
                            }]
                        }
                    }
                });
            }
        });
    });
    $("#graph2").click(function () {
        let start=$("#startdate").val();
        let end=$("#enddate").val();
        let exp=$("#expfilt").val();
        $.ajax({
            url: 'api/stat.php',
            data: {
                action:"get_stat_exp",
                start:start,
                end:end,
                exp:JSON.stringify(exp)
            },
            type: 'POST',
            error: function(res){
                console.log(res);
            },
            success: function (res) {
                res=JSON.parse(res);
                let marks = res.stat.marks;
                let time = res.stat.time;
                let markcan = document.getElementById('markgraph');
                let timecan = document.getElementById('timegraph');
                markgraph.destroy();
                timegraph.destroy();
                timecan.getContext('2d').clearRect(0,0,timecan.width,timecan.height);
                let marklabels=[];
                let timelabels=[];
                let markdata=[];
                let timedata=[];
                for (let i = 0; i < marks.length; i++) {
                    markdata.push(marks[i]['mark']);
                    marklabels.push(marks[i]['name']);
                }
                for (let i = 0; i < marks.length; i++) {
                    timedata.push(time[i]['dev']);
                    timelabels.push(time[i]['name']);
                }
                const colours = timedata.map((value) => value < 0 ? 'red' : 'green');
                markgraph = new Chart(markcan.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: marklabels,
                        datasets: [{
                            label: 'Оценка',
                            data: markdata,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks:{
                                    min:0,
                                    max:6
                                }
                            }]
                        }
                    }
                });
                timegraph = new Chart(timecan.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: timelabels,
                        datasets: [{
                            label: 'Среднее отклонение',
                            data: timedata,
                            backgroundColor: colours,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks:{
                                    min:Math.min(...timedata)-1,
                                    max:Math.max(...timedata)+1
                                }
                            }]
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>