/*
 * Copyright (c) 2013 Pavle Goloskokovic
 *
 * Distributed under the terms of the MIT license.
 * http://www.opensource.org/licenses/mit-license.html
 */

var canvas = document.getElementById("spaceLines");
var p = new Processing(canvas, init);

function init(p){

    var NODE_CNT = 4,
        NODE_CNT_MAX = 8,

        NODE_DIR_MAX = 8,
        DIR_TOP_LEFT = 0,
        DIR_TOP = 1,
        DIR_TOP_RIGHT = 2,
        DIR_LEFT = 3,
        DIR_RIGHT = 5,
        DIR_BOTTOM_LEFT = 6,
        DIR_BOTTOM = 7,
        DIR_BOTTOM_RIGHT = 8;

    var step = 8;
    var stageHeight = 500;
    var stageWidth = 500;

    var pixelHeight = (stageHeight/step|0) - 1;
    var pixelWidth = (stageWidth/step|0) - 1;

    var paddingX = (stageWidth - (pixelWidth-1)*step)/2|0;
    var paddingY = (stageHeight - (pixelHeight-1)*step)/2|0;

    var x=pixelWidth/2|0;
    var y=pixelHeight/2|0;

    var FPS = 45;

    var img;
    var loaded = false;

    var nodes = [];
    var nodesTotal = pixelHeight*pixelWidth;
    var nodesTraversed = 0;
    var currentNode;

    var iterations = 0;
    var iterationsTotal = NODE_DIR_MAX/2*nodesTotal;

    p.setup = function(){

        // Load image

        var imgUrl = p.param("img");
        if (imgUrl == null){
            imgUrl = "default.jpg";
        }

        img = p.loadImage(imgUrl, undefined, function(){
            img.resize(pixelWidth,pixelHeight);
            img.loadPixels();
            loaded = true;
        });

        // Init data nodes

        for(var i=nodesTotal-1; i>=0; i--){
            nodes[i] = [0,0,0,0,0,0,0,0,0];
            /*
             *  Node structure
             *  [(0)(1)(2)]   [ \  |  / ]
             *  [(3)(4)(5)] = [ - CNT - ]
             *  [(6)(7)(8)]   [ /  |  \ ]
             */
        }
        currentNode = nodes[y*pixelWidth + x];

        // Init Processing parameters, draw background and stars

        p.smooth();
        p.frameRate(FPS);
        p.background(17);
        p.size(stageWidth, stageHeight);

        p.fill(255);
        p.stroke(17);
        for(i=0; i<300;i++){
            var dim = p.random(0, 3);
            p.fill(p.random(225, 255));
            p.ellipse(p.random(0, stageWidth), p.random(0, stageHeight), dim, dim);
        }

        // Create helper canvas for image manipulation

        var helperCanvas = document.createElement('canvas');
        var helperCtx = helperCanvas.getContext('2d');
        helperCanvas.height = stageHeight;
        helperCanvas.width = stageWidth;

        // Set generated stars image as body background

        helperCtx.drawImage(canvas, 0, 0);
        helperCtx.fillStyle = "rgba(17,17,17,0.65)";
        helperCtx.fillRect(0,0,stageWidth,stageHeight);
        document.body.style.backgroundImage = "url('"+helperCanvas.toDataURL("image/png")+"')";

        /*
        // Draw pixel numbers

        for(var x = 0; x<pixelWidth; x++){
            for(var y = 0; y<pixelHeight; y++){
                var n = x + y*pixelWidth;
                p.text(n+'', x*step + step/4,y*step + step/4);
            }
        }
        */

        // Set helper canvas context properties

        helperCtx.textAlign = "right";
        helperCtx.textBaseline = "bottom";
        helperCtx.fillStyle="#FFFFFF";
        helperCtx.font = "normal "+ (paddingY-1) +"px sans-serif";

        // Bind save button onclick event

        document.getElementById("save").onclick = function(){

            // Copy image from main canvas to canvas for saving image
            // and add url in the bottom right corner

            helperCtx.drawImage(canvas, 0, 0);
            helperCtx.fillText("http://processing.kestenpire.com", stageWidth-paddingX, stageHeight);

            // Open new window and write image in it

            var w=window.open('','_blank');
            w.document.write("<img src='"+helperCanvas.toDataURL("image/png")+"'/>");
        }
    };

    p.draw = function(){

        // Check if image is loaded

        if(loaded){

            var direction,
                nextDirection,
                nextX, nextY,
                xStart, yStart,
                xEnd, yEnd,
                nextNode;

            // Check if current node was traversed maximum number of times

            if(currentNode[NODE_CNT] == NODE_CNT_MAX){

                // Randomly pick new current node

                x = p.random(pixelWidth)|0;
                y = p.random(pixelHeight)|0;

                var n = y*pixelWidth + x;

                currentNode = nodes[n];

                // Iterate data nodes to find new current node
                // if randomly picked one was also traversed maximum number of times

                while(currentNode[NODE_CNT] == NODE_CNT_MAX){
                    n = (n+1) % nodesTotal;
                    currentNode = nodes[n];
                }

                // Update x and y for new current node

                x = n % pixelWidth;
                y = n / pixelWidth | 0;
            }

            // Choose random direction

            direction = p.random(NODE_DIR_MAX)|0;
            if(direction>=NODE_CNT){
                direction++;
            }

            // Find direction that was not traversed
            // if random direction already was

            if(currentNode[direction]>0){
                for(var i=0; i<NODE_DIR_MAX-1; i++){
                    direction = (direction + 1) % (NODE_DIR_MAX + 1);
                    if(direction == NODE_CNT) direction++;
                    if(currentNode[direction]==0) break;
                }
            }

            // Assign direction for next node

            nextDirection = NODE_DIR_MAX - direction; // 8-[0:8]

            // Assign starting point for drawing

            xStart = x*step;
            yStart = y*step;

            // Calculate x coordinate for next node and end drawing point

            switch(direction){
                case    DIR_TOP_LEFT:
                case        DIR_LEFT:
                case DIR_BOTTOM_LEFT:

                    if(x==0){
                        nextX = pixelWidth-1;
                        xEnd = 0;
                    }else{
                        nextX = x-1;
                        xEnd = nextX * step;
                    }

                    break;

                case DIR_TOP:
                case DIR_BOTTOM:

                    nextX = x;
                    xEnd = nextX * step;
                    break;

                case    DIR_TOP_RIGHT:
                case        DIR_RIGHT:
                case DIR_BOTTOM_RIGHT:

                    if(x==pixelWidth-1){
                        nextX = 0;
                        xEnd = x*step;
                    }else{
                        nextX = x+1;
                        xEnd = nextX * step;
                    }

                    break;
            }

            // Calculate y coordinate for next node and end drawing point

            switch(direction){
                case DIR_TOP_LEFT:
                case DIR_TOP:
                case DIR_TOP_RIGHT:

                    if(y==0){
                        nextY = pixelHeight-1;
                        yEnd = 0;
                    }else{
                        nextY = y-1;
                        yEnd = nextY * step;
                    }
                    break;

                case DIR_LEFT:
                case DIR_RIGHT:

                    nextY = y;
                    yEnd = nextY * step;
                    break;

                case DIR_BOTTOM_LEFT:
                case DIR_BOTTOM:
                case DIR_BOTTOM_RIGHT:

                    if(y==pixelHeight-1){
                        nextY = 0;
                        yEnd = y * step;
                    }else{
                        nextY = y+1;
                        yEnd = nextY * step;
                    }
                    break;
            }

            // Increment traversal counter and set traversed direction for current node

            currentNode[direction]++;
            currentNode[NODE_CNT]++;

            // If node is fully traversed increment total number of fully traversed nodes

            if(currentNode[NODE_CNT] == NODE_CNT_MAX) nodesTraversed++;


            // Get the next node and update it same as the current node

            nextNode = nodes[nextY*pixelWidth + nextX];

            nextNode[nextDirection]++;
            nextNode[NODE_CNT]++;

            if(nextNode[NODE_CNT] == NODE_CNT_MAX) nodesTraversed++;


            // Draw line

            p.stroke(img.pixels.getPixel(y*pixelWidth + x));
            p.line(paddingX + xStart, paddingY + yStart,
                   paddingX + xEnd,   paddingY + yEnd );


            // If all nodes are fully traversed
            // update status and exit

            if(nodesTraversed == nodesTotal){
                updateStatus("Done!");
                p.exit();
                return;
            }

            // Move current node to next node

            x = nextX;
            y = nextY;
            currentNode = nextNode;


            // Update iterations counter and update progress status

            iterations++;

            var percentCompleted = (100*iterations/iterationsTotal).toFixed(2);
            updateStatus("Generating... " + percentCompleted + "%");
        }

    };

    function updateStatus(status){
        document.getElementById("status").innerHTML = status;
    }
}

/*
 console.log("Going from node " + (x + y*pixelWidth) + ' in direction ' + direction);
 console.log("...to node " + (newX + newY*pixelWidth) + ' in direction ' + newDirection);
 console.log("BEFORE " + (x + y*pixelWidth));
 console.log('[' + (node[0]==0?" ":"\\") + ", " + (node[1]==0?" ":"|") + ", " + (node[2]==0?" ":"/") + ']');
 console.log('[' + (node[3]==0?" ":"-")  + ", " +        node[4]       + ", " + (node[5]==0?" ":"-") + ']');
 console.log('[' + (node[6]==0?" ":"/")  + ", " + (node[7]==0?" ":"|") + ", " + (node[8]==0?" ":"\\")+ ']');
 */
/*
 console.log("BEFORE " + (newX + newY*pixelWidth));
 console.log('[' + (newNode[0]==0?" ":"\\") + ", " + (newNode[1]==0?" ":"|") + ", " + (newNode[2]==0?" ":"/") + ']');
 console.log('[' + (newNode[3]==0?" ":"-")  + ", " +        newNode[4]       + ", " + (newNode[5]==0?" ":"-") + ']');
 console.log('[' + (newNode[6]==0?" ":"/")  + ", " + (newNode[7]==0?" ":"|") + ", " + (newNode[8]==0?" ":"\\")+ ']');
 */
/*
 console.log("AFTER " + (x + y*pixelWidth));
 console.log('[' + (node[0]==0?" ":"\\") + ", " + (node[1]==0?" ":"|") + ", " + (node[2]==0?" ":"/") + ']');
 console.log('[' + (node[3]==0?" ":"-")  + ", " +        node[4]       + ", " + (node[5]==0?" ":"-") + ']');
 console.log('[' + (node[6]==0?" ":"/")  + ", " + (node[7]==0?" ":"|") + ", " + (node[8]==0?" ":"\\")+ ']');
 console.log("BEFORE " + (newX + newY*pixelWidth));
 console.log('[' + (newNode[0]==0?" ":"\\") + ", " + (newNode[1]==0?" ":"|") + ", " + (newNode[2]==0?" ":"/") + ']');
 console.log('[' + (newNode[3]==0?" ":"-")  + ", " +        newNode[4]       + ", " + (newNode[5]==0?" ":"-") + ']');
 console.log('[' + (newNode[6]==0?" ":"/")  + ", " + (newNode[7]==0?" ":"|") + ", " + (newNode[8]==0?" ":"\\")+ ']');
 */