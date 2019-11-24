<html>
<head>
<title>Just a simple game!</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<style>
canvas {
    border: 1px solid #d3d3d3;
    background-color: #f1f1f1;
}
</style>

</head>
<body onload="testFunction()">


<script>
var myGamePiece;
var myOil = [];
var myCoin = [];
var myObstacles = [];
var myScore;
var score_count;
var myBackground;
var mySound;
var myMusic;
var nc;
var fc;
var myGamePiece = new component(30, 16, "img/car_r_1.png", 10, 120, "image");

//restart added
function restartGame() {
	myObstacles = [];
	myOil = [];
	myCoin = [];
	score_count = 0;
	myGameArea.clear();
	myGameArea.stop();
	startGame();
}

function startGame() {
	mySound = new sound("sound/crash.mp3");
	slipSound = new sound("sound/slip.mp3");
	coinSound = new sound("sound/coin.mp3");
	document.getElementById("myfilter").style.display = "none";
	document.getElementById("myrestartbutton").style.display = "none";
	document.getElementById("canvascontainer").innerHTML = "";
	myMusic = new sound("sound/gametheme.mp3");
	myScore = new component("30px", "Consolas", "black", 250, 40, "text");
	score_count = 0;
	myBackground = new component(852, 270, "img/background.jpg", 0, 0, "background");
	myMusic.loop = true;	
	myMusic.play();
	myGameArea.start();
}

function sound(src) {
  	this.sound = document.createElement("audio");
  	this.sound.src = src;
  	this.sound.setAttribute("preload", "auto");
  	this.sound.setAttribute("controls", "none");
  	this.sound.style.display = "none";
  	document.body.appendChild(this.sound);
  	this.play = function(){
    		this.sound.play();
  	}
  	this.stop = function(){
    		this.sound.pause();
  	}
} 

function write_score() {
	name = document.getElementById("fname").value;
	name = name.substring(0, 30);
	score = myGameArea.frameNo;
   	$.post("save.php", {Name: name, Score: score}, function(data){
   		console.log('response from the callback function: '+ data);
		document.getElementById("demoq").innerHTML = data;
   	}).fail(function(jqXHR){
     	alert(jqXHR.status +' '+jqXHR.statusText+ ' $.post failed!');
  	});    	
	}

function everyinterval(n) {
  if ((myGameArea.frameNo / n) % 1 == 0) {return true;}
  return false;
}

function component(width, height, color, x, y, type) {
	this.type = type; 
	if (type == "image" || type == "background") {
		this.image = new Image();
    		this.image.src = color;
  	} 	
	this.width = width;
	this.height = height;
	this.color = color;
	this.speedX = 0;
	this.speedY = 0;
  	this.x = x;
  	this.y = y;
  	this.update = function() {
    		ctx = myGameArea.context;
		if (this.type == "text") {
      			ctx.font = this.width + " " + this.height;
      			ctx.fillStyle = color;
      			ctx.fillText(this.text, this.x, this.y);
    		} else if (type == "image" || type == "background") {
      			ctx.drawImage(this.image, this.x, this.y, this.width, this.height);
			if (type == "background") {
			        ctx.drawImage(this.image, this.x + this.width, this.y, this.width, this.height);
      			}
    		} else {
    		ctx.fillStyle = this.color;
    		ctx.fillRect(this.x, this.y, this.width, this.height);
		}
  	}
  	this.newPos = function() {
		this.x += this.speedX;
    		this.y += this.speedY;
		if (this.type == "background") {
		      	if (this.x == -(this.width)) {
				this.x = 0;
      			}
    		}
  	}
 	this.crashWith = function(otherobj) {
    		var myleft = this.x;
    		var myright = this.x + (this.width);
    		var mytop = this.y;
    		var mybottom = this.y + (this.height);
    		var otherleft = otherobj.x;
    		var otherright = otherobj.x + (otherobj.width);
    		var othertop = otherobj.y;
    		var otherbottom = otherobj.y + (otherobj.height);
    		var crash = true;
    		if ((mybottom < othertop) || (mytop > otherbottom) || (myright < otherleft) || (myleft > otherright)) {
			
      			crash = false;
			
    		} 
		
    		return crash;
  	}

}

var myGameArea = {
  	canvas : document.createElement("canvas"),
  	start : function() {
    		this.canvas.width = 480;
    		this.canvas.height = 270;
		document.getElementById("canvascontainer").appendChild(this.canvas);
    		this.context = this.canvas.getContext("2d");
		this.frameNo = 0;
		//this.score = 0;
    		this.interval = setInterval(updateGameArea, 15);
    		window.addEventListener('keydown', function (e) {
      			myGameArea.keys = (myGameArea.keys || []);
      			myGameArea.keys[e.keyCode] = true;
			//Force new image of gamepiece
			if (myGameArea.keys[39]) {
			myGamePiece.image.src = "img/fast/" + fc;			
			myGamePiece.width = 50; 
			}			
			
			
			
    		})
    		window.addEventListener('keyup', function (e) {
      			myGameArea.keys[e.keyCode] = false;
			myGamePiece.width = 30; myGamePiece.image.src = "img/" + nc;
    		})
  	},
  	clear : function(){
    		this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
  	},
	stop : function() {
		clearInterval(this.interval);
		
  	}
}

function playerSettings() {
  	nc = document.getElementById("carSelect").value;
	fc = "f"+ nc;
	myGamePiece.image.src = "img/" + nc;
	var xi = document.getElementById("fname").value;
  	document.getElementById("demoq").innerHTML = "<img src=\"img/"+ nc +"\"> "+ xi;
	}

function updateGameArea() {
  	var x, y;
  	for (i = 0; i < myObstacles.length; i += 1) {
    		if (myGamePiece.crashWith(myObstacles[i])) {
			write_score();      			
			myGameArea.stop();
			mySound.play();
			myMusic.stop();
			//two lines added for restart
			document.getElementById("myfilter").style.display = "block";
            		document.getElementById("myrestartbutton").style.display = "block";
      			return;
    		}
  	}
	for (i = 0; i < myOil.length; i += 1) {
    		if (myGamePiece.crashWith(myOil[i])) { 
		slipSound.play();
		var random = Math.floor(Math.random() * 30) - 15;
		myGamePiece.speedX = 3;
		myGamePiece.speedY = random;
		myGamePiece.newPos();
  		myGamePiece.update();
		return;
    		}
  	}
	for (i = 0; i < myCoin.length; i += 1) {
    		if (myGamePiece.crashWith(myCoin[i])) { 
		coinSound.play();
		score_count = score_count + 200;
		myCoin[i].x = -20;	
		myCoin[i].y = -20;
		return;
    		}
  	}
  	myGameArea.clear();
	myBackground.newPos();
  	myBackground.update();
	inc_speed = 150 -(myGameArea.frameNo/100);
	inc_speed = parseInt(inc_speed);	
	myBackground.speedX = -1.5; 
	myOil.x += -1.5;
  	myGameArea.frameNo += 1;
	if (myGameArea.frameNo == 1 || everyinterval(130)) {
    		x = myGameArea.canvas.width;
		//Added for restart frame
		y = myGameArea.canvas.height - 100;
    		minHeight = 20;
    		maxHeight = 200;
    		height = Math.floor(Math.random()*(maxHeight-minHeight+1)+minHeight);
    		minGap = 50;
    		maxGap = 200;
    		gap = Math.floor(Math.random()*(maxGap-minGap+1)+minGap);
    		myObstacles.push(new component(10, height, "#333333", x, 0));
    		myObstacles.push(new component(10, x - height - gap, "#333333", x, height + gap));
  	}
	// OIL
	
	if (myGameArea.frameNo == 1 || everyinterval(inc_speed)) {
    		x = myGameArea.canvas.width;
		y = myGameArea.canvas.height - 100;
    		minHeight = 10;
    		maxHeight = 230;
		height = Math.floor(Math.random() * maxHeight) + minHeight;
		myOil.push(new component(30, 30, "img/oil.png", x, height, "image"));
  	}
	if (myGameArea.frameNo == 1 || everyinterval(170)) {
    		x = myGameArea.canvas.width;
		y = myGameArea.canvas.height - 100;
    		minHeight = 10;
    		maxHeight = 230;
		height = Math.floor(Math.random() * maxHeight) + minHeight;
		myCoin.push(new component(30, 30, "img/coin.png", x, height, "image"));
  	}
  	for (i = 0; i < myObstacles.length; i += 1) {
    		myObstacles[i].x += -1.5;
    		myObstacles[i].update();
  	}
	for (i = 0; i < myOil.length; i += 1) {
    		myOil[i].x += -1.5;
    		myOil[i].update();
  	}
	for (i = 0; i < myCoin.length; i += 1) {
    		myCoin[i].x += -1.5;
    		myCoin[i].update();
  	}
  	 
	myGamePiece.speedX = 0;
  	myGamePiece.speedY = 0;
    	if (myGameArea.keys && myGameArea.keys[37]) {myGamePiece.speedX = -1; }
  	if (myGameArea.keys && myGameArea.keys[39]) {myGamePiece.speedX = 1; }
  	if (myGameArea.keys && myGameArea.keys[38]) {myGamePiece.speedY = -1; }
  	if (myGameArea.keys && myGameArea.keys[40]) {myGamePiece.speedY = 1; }
	
  	
    	myScore.text = "SCORE: " + score_count;
  	myScore.update();
	myGamePiece.newPos();
  	myGamePiece.update();

} 




// Button controls function
function myFunction() {
  	var nc = document.getElementById("carSelect").value;
	var fc = "f"+ nc;
	var xi = document.getElementById("carSelect").selectedIndex;
  document.getElementById("demoq").innerHTML = nc;
	
}




function testFunction(onclick){
  var x = document.getElementById("myDIV");
	nc = "car_r_1.png";
	fc = "f"+ nc;
	
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}

</script>


<table width="100%"><tr><td valign="top">

<div id="myfilter" style="position:absolute;background-color:#000000;opacity:0.3;width:482px;height:272px;display:none"></div>
<div id="myrestartbutton" style="position:absolute;padding-top:130px;padding-left:205px;display:none;"><button onclick="restartGame()">Restart</button></div>
<div id="canvascontainer"></div>

<div style="text-align:center;width:480px;" id="controls"></div>


<div><button onclick="startGame()">Start</button><button onclick="testFunction()">Settings</button></div>

<div id="myDIV">
<table><tr><td valign="top">
Choose a car: </td><td> 
<select id="carSelect" size="5">
  	<option selected value="car_r_1.png">Red</option>
  	<option value="car_g_1.png">Green</option>
  	<option value="car_b_1.png">Blue</option>
  	<option value="car_p_1.png">Pink</option>
	<option value="car_y_1.png">yellow</option>
</select>
</td>


<td valign="top">Your Name:</td><td valign="top"><input type="text" id="fname" name="fname"></td><td valign="top">
<button onclick="playerSettings()">Try it</button>
</td></tr></table>
<p id="demoq"></p>
</div>
</td><td valign="top">
<div id="Scoreboeard"><?php include("get.php"); ?></div>
</td></tr></table>

</body>
</html>
