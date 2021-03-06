<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>HTML5 Skin Editor</title>
</head>
<body>

<table>
<tr>
<td>
<canvas id="editor" width="513" height="257"></canvas><br>
<canvas id="colors" width="513" height="129"></canvas>
</td>
<td>
<canvas id="preview" width="132" height="128"></canvas><br><br>
<button type="button" onclick="tool=1">Eraser</button><br>
<button type="button" onclick="tool=2">Color Picker</button><br><br>
<button type="button" onclick="noise()">Add Noise</button><br>
<button type="button" onclick="save()">Save skin</button>
</td>
</tr>
</table>

HTML5 Skin Editor - Daily version<br>
Works best on Chrome, Safari and Firefox

<script type="text/javascript">
//Canvas variables
var emt_char=document.createElement("canvas");
emt_char.width="64";
emt_char.height="32";
var ctx_char=emt_char.getContext("2d");
var emt_editor=document.getElementById("editor");
var ctx_editor=emt_editor.getContext("2d");
var emt_preview=document.getElementById("preview");
var ctx_preview=emt_preview.getContext("2d");
var emt_colors=document.getElementById("colors");
var ctx_colors=emt_colors.getContext("2d");
var pxl_colors;

//Another variables
var selecting=false;
var drawing=false;
var erasing=false;
var tool=0;	// 0=pen, 1=eraser, 2=picker

var mouseX=0;
var mouseY=0;

//Image preloader
var img_char=new Image();
var img_grid=new Image();
var img_colors=new Image();
img_char.src="<?php
if($_GET["username"]==null) echo("char.png");
else echo("getskin.php?username=".$_GET["username"]);
?>";
img_char.onload=function() {
	img_grid.src="grid.png";
	img_grid.onload=function() {
		img_colors.src="colors.png";
		img_colors.onload=function() { start(); }
	}
}

function preview(x1,y1,width,height,x2,y2)
{
	var pixels=ctx_char.getImageData(x1,y1,width,height);
	ctx_preview.putImageData(pixels,x2,y2);
}

function hatview(x1,y1,width,height,x2,y2)
{
	var pxl_char=ctx_char.getImageData(x1,y1,width,height);
	var pxl_preview=ctx_preview.getImageData(x2,y2,width,height);
	
	//Alpha-blending
	for(var x=0;x<pxl_char.width;x++)
	{
		for(var y=0;y<pxl_char.height;y++)
		{
			var index=(x+y*pxl_char.width)*4;
			var alpha=pxl_char.data[index+3]/255;

			pxl_char.data[index+0]=alpha*pxl_char.data[index+0]+(1-alpha)*pxl_preview.data[index+0];
			pxl_char.data[index+1]=alpha*pxl_char.data[index+1]+(1-alpha)*pxl_preview.data[index+1];
			pxl_char.data[index+2]=alpha*pxl_char.data[index+2]+(1-alpha)*pxl_preview.data[index+2];
			pxl_char.data[index+3]=255;
		}
	}

	ctx_preview.putImageData(pxl_char,x2,y2);
}


//Function called after preload
function start()
{
	//Editor
	ctx_char.drawImage(img_char,0,0);
	refresh();
	
	//Color selector
	ctx_colors.drawImage(img_colors,0,0);
	pxl_colors=ctx_colors.getImageData(0,0,emt_colors.width,emt_colors.height);
}

//Function called during refresh
function refresh()
{
	//Editor
	ctx_editor.clearRect(0,0,emt_editor.width,emt_editor.height);
	var pixels=ctx_char.getImageData(0,0,emt_char.width,emt_char.height);
	for(var x=0;x<pixels.width;x++) 
	{
		for(var y=0;y<pixels.height;y++)
		{
			var index=(x+y*pixels.width)*4;
			ctx_editor.fillStyle="rgba("+pixels.data[index+0]+","+ 
										 pixels.data[index+1]+","+
										 pixels.data[index+2]+","+
										 pixels.data[index+3]+")";
				
			ctx_editor.fillRect(x*8,y*8,8,8);
		}
	}
				
	ctx_editor.drawImage(img_grid,0,0);
	ctx_editor.fillStyle="rgba(0,0,0,0.5)";
	ctx_editor.fillRect(mouseX/8*8,mouseY/8*8,8,8);
	
	//Preview
	ctx_preview.clearRect(0,0,36,32);
	
	preview(8,8,8,8,4,0);		//head
	hatview(40,8,8,8,4,0);		//fhat
	preview(44,20,4,16,0,8);	//larm
	preview(20,20,8,12,4,8);	//body
	preview(44,20,4,16,12,8);	//rarm
	preview(4,20,4,12,4,20);	//lleg
	preview(4,20,4,12,8,20);	//rleg
	
	preview(24,8,8,8,21,0);		//head
	hatview(56,8,8,8,21,0);		//fhat
	preview(52,20,4,16,17,8);	//larm
	preview(32,20,8,12,21,8);	//body
	preview(52,20,4,16,29,8);	//rarm
	preview(12,20,4,12,21,20);	//lleg
	preview(12,20,4,12,25,20);	//rleg
	
	var pixels=ctx_preview.getImageData(0,0,36,32);
	ctx_preview.clearRect(0,0,36,32);

	for(var x=0;x<pixels.width;x++) 
	{
		for(var y=0;y<pixels.height;y++)
		{
			var index=(x+y*pixels.width)*4;
			ctx_preview.fillStyle="rgba("+pixels.data[index+0]+","+ 
										  pixels.data[index+1]+","+
										  pixels.data[index+2]+","+
										  pixels.data[index+3]+")";
				
			ctx_preview.fillRect(x*4,y*4,4,4);
		}
	}
}

emt_editor.onmousedown=function(e)
{
	e.preventDefault();
	
	if(tool==0)
	{
		drawing=true;
		emt_editor.onmousemove(e);
	}
	
	if(tool==1)
	{
		erasing=true
		emt_editor.onmousemove(e);
	}
	
	if(tool==2)
	{
		var pixels=ctx_char.getImageData(0,0,emt_char.width,emt_char.height);
		var index=(mouseX/8+mouseY/8*pixels.width)*4;
		ctx_char.fillStyle="rgba("+pixels.data[index+0]+","+ 
								   pixels.data[index+1]+","+
								   pixels.data[index+2]+","+
								   pixels.data[index+3]+")";
	
		ctx_colors.drawImage(img_colors,0,0);
		tool=0;
	}
}

emt_editor.onmouseup=emt_editor.onmouseout=function(e)
{
	if(drawing) drawing=false;
	if(erasing)
	{
		erasing=false;
		tool=0;
	}
}

emt_editor.onmousemove=function(e)
{
	mouseX=e.pageX-this.offsetLeft-10;	//do naprawy
	mouseY=e.pageY-this.offsetTop-10;	//do naprawy
	mouseX-=mouseX%8;mouseY-=mouseY%8;
	
	if(drawing) ctx_char.fillRect(mouseX/8,mouseY/8,1,1);
	if(erasing)
	{
		var pixels=ctx_char.createImageData(1,1);
		ctx_char.putImageData(pixels,mouseX/8,mouseY/8);
	}
	refresh();
}

emt_colors.onmousedown=function(e)
{
	e.preventDefault();
	
	selecting=true;
	emt_colors.onmousemove(e);
}

emt_colors.onmouseup=emt_colors.onmouseout=function(e)
{
	selecting=false;
}

emt_colors.onmousemove=function(e)
{
	if(selecting)
	{
		var index=((e.pageX-this.offsetLeft-10)+(e.pageY-this.offsetTop-10)*pxl_colors.width)*4;
		ctx_char.fillStyle="rgb("+pxl_colors.data[index+0]+","+pxl_colors.data[index+1]+","+pxl_colors.data[index+2]+")";
		ctx_colors.drawImage(img_colors,0,0);

		ctx_colors.beginPath();
		ctx_colors.arc(e.pageX-this.offsetLeft-10,e.pageY-this.offsetTop-10,5,0,Math.PI*2,true);
		ctx_colors.closePath();
	
		ctx_colors.strokeStyle="rgba(0,0,0,0.3)";
		ctx_colors.fillStyle="rgba(0,0,0,0.2)";
		ctx_colors.stroke();
		ctx_colors.fill();
	}
}

function noise()
{
	var pixels=ctx_char.getImageData(0,0,emt_char.width,emt_char.height);
	for(var x=0;x<pixels.width;x++)
	{
		for(var y=0;y<pixels.height;y++)
		{
			var seed=Math.random()*16-8;
			var index=((x+y*pixels.width)*4);
			pixels.data[index+0]=pixels.data[index+0]+seed;
			pixels.data[index+1]=pixels.data[index+1]+seed;
			pixels.data[index+2]=pixels.data[index+2]+seed;
		}
	}
	ctx_char.putImageData(pixels,0,0);
	refresh();
}

function save()
{
	window.location=emt_char.toDataURL("image/png");  
}
</script>

</body>
</html>