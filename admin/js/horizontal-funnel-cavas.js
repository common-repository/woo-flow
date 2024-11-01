var canvas = document.getElementById("wf-funnel");
if (canvas) {
    var ctx = canvas.getContext("2d");
    var cw = canvas.width;
    var ch = canvas.height;

    var height = canvas.height;
    var baseY = canvas.height;
    var data = [0, 0, 0, 0];
    console.log(funnel_data);
    for (var i = 0; i < funnel_data.length; i++) {
        data[i] = parseInt(funnel_data[i]);
    }

    filledLineChart(data, height, baseY, 'gold');

    function filledLineChart(data, height, baseY, fillcolor) {
        var stepWidth = canvas.width / (data.length - 1);
        var maxDataValue = 0;
        for (var i = 0; i < data.length; i++) {
            if (data[i] > maxDataValue) {
                maxDataValue = data[i];
            }
        }
        ctx.beginPath();
        ctx.moveTo(0, baseY - height * data[0] / maxDataValue);
        for (var i = 1; i < data.length; i++) {
            ctx.lineTo(stepWidth * i, baseY - height * data[i] / maxDataValue);
        }
        ctx.lineTo(stepWidth * (data.length - 1), baseY);
        ctx.lineTo(0, baseY);
        ctx.fillStyle = fillcolor;
        ctx.fill();
        ctx.strokeStyle = '#c0c0c0';
        ctx.lineWidth = 1;
        ctx.beginPath();
        for (var i = 1; i < data.length - 1; i++) {
            ctx.moveTo(i * stepWidth - 1, baseY - height);
            ctx.lineTo(i * stepWidth - 1, baseY);
        }
        ctx.stroke();
    }
}