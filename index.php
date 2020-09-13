<?php
  require_once "dbconnect.php";

  $sql = "SELECT * FROM `pj-events`";
  $events = $DB->Execute($sql);
  $eventIds = array();
  $eventNames = array();

  $totalObject = array();
  $imageObject = array();
  $imageArray = array();
  $check = "";
  $value = "";
  $name = "";
  $xVal = "";
  $yVal = "";

  $newestColor = "style='background-color: red; color: white;'";

  $sqlStatement = "SELECT * FROM `ImagesView` ORDER BY EID, Date ASC";
  if(isset($_POST['newest'])){
    $sqlStatement = "SELECT * FROM `ImagesView` ORDER BY EID, Date ASC";
    $newestColor = "style='background-color: red; color: white;'";
    $oldestColor = "";
  }

  if(isset($_POST['oldest'])){
    $sqlStatement = "SELECT * FROM `ImagesView` ORDER BY EID, Date DESC";
    $oldestColor = "style='background-color: red; color: white;'";
    $newestColor = "";
  }

  $sqlStatement = $DB->Execute($sqlStatement);
  $recordCount = $sqlStatement->recordCount();

  foreach ($sqlStatement as $key => $image) {
    $value = $image['EID'];

    if(!$check){
      array_push($imageArray, array("fileName" => $image['FileName'], "Date" => $image['Date']));
    }

    if($value === $check){
      array_push($imageArray, array("fileName" => $image['FileName'], "Date" => $image['Date']));
    }

    if(($value !== $check) && $check){
      array_push($totalObject, array("EID" => $check, "EventName" => $name, "xVal" => $xVal, "yVal" => $yVal, "images" => $imageArray));
      $imageArray = array();
      array_push($imageArray, array("fileName" => $image['FileName'], "Date" => $image['Date']));
    }

    if($key === $recordCount-1){
      array_push($totalObject, array("EID" => $image['EID'], "EventName" => $image['Name'], "xVal" => $image['xVal'], "yVal" => $image['yVal'], "images" => $imageArray));
      $imageArray = array();
    }
    $name = $image['Name'];
    $xVal = $image['xVal'];
    $yVal = $image['yVal'];
    $check = $value;
  }

  $test = json_encode($totalObject);

  $event = "";
  if(isset($_POST['submit'])){
    $today = date("Y/m/d");
    $event = $_POST['events'];
    // Configure upload directory and allowed file types
    $upload_dir = './Assets/Images/';
    $allowed_types = array('jpg', 'png', 'jpeg', 'gif');

    // Define maxsize for files i.e 2MB
    //$maxsize = 2 * 1024 * 1024;

    // Checks if user sent an empty form
    if(!empty(array_filter($_FILES['files']['name']))) {

        // Loop through each file in files[] array
        foreach ($_FILES['files']['tmp_name'] as $key => $value) {

            $file_tmpname = $_FILES['files']['tmp_name'][$key];
            $file_name = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Set upload file path
            $filepath = $upload_dir.$file_name;

            // Check file type is allowed or not
            if(in_array(strtolower($file_ext), $allowed_types)) {

                // If file with name already exist then append time in
                // front of name of the file to avoid overwriting of file
                if(file_exists($filepath)) {
                    $fileName = time().$file_name;
                    $filepath = $upload_dir.$fileName;

                    if( move_uploaded_file($file_tmpname, $filepath)) {
                        $insertImage = "INSERT INTO `pj-images` (EID, FileName, Date) VALUES ({$event}, '{$fileName}', '{$today}')";

                        try{
                          $insertImage = $DB->Execute($insertImage);
                          header("Refresh:0");
                        }catch(Exception $e){
                          print $e;
                        }
                    }
                    else {
                        echo "Error uploading {$file_name} <br />";
                    }
                }
                else {

                    if( move_uploaded_file($file_tmpname, $filepath)) {
                      $insertImage = "INSERT INTO `pj-images` (EID, FileName, Date) VALUES ({$event}, '{$file_name}', '{$today}')";

                      try{
                        $insertImage = $DB->Execute($insertImage);
                        header("Refresh:0");
                      }catch(Exception $e){
                        print $e;
                      }
                    }
                    else {
                        echo "Error uploading {$file_name} <br />";
                    }
                }
            }
            else {

                // If file extention not valid
                echo "Error uploading {$file_name} ";
                echo "({$file_ext} file type is not allowed)<br / >";
            }
        }
    }
    else {

        // If no files selected
        echo "No files selected.";
    }
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <title>Image Viewer</title>
  </head>
  <body>
    <?php print "<script>let test = $test</script>"; ?>
    <div class="row" style="height: 100vh; width: 100%;" >
      <div class="col-8 border">
        <div style="display: none;">
          <img id="state"  src="./Assets/indiana.png" alt="The Scream" style="display: block; width:400px; height: 600px; margin-left: auto; margin-right: auto; vertical-align: middle;">
        </div>

        <canvas id="canvas" width="500" height="700" style=" display: block; margin-left: auto; margin-right: auto; vertical-align: middle;"></canvas>
      </div>
      <div class="col-4">
        <form class="mt-4 text-center" action="index.php" method="POST">
          <h3>Filter</h3>
          <button type="submit" name="newest" id="newest" class="btn btn-light mr-4 border border-dark" <?php print $newestColor; ?>>Newest Image</button>
          <button type="submit" name="oldest" id="oldest" class="btn btn-light mr-4 border border-dark" <?php print $oldestColor; ?>>Oldest Image</button>
        </form>

        <hr>
        <div class="text-center">
          <h3>Add New Images</h3>
          <div class="form-group mx-5">
            <form action="index.php" method="POST" enctype="multipart/form-data">
              <label for="events" class="mt-2">Select Event</label>
              <select class="form-control" id="events" name="events">
                <?php
                  foreach ($totalObject as $eventKey => $eventVal) {
                    print "<option value='".$eventVal['EID']."'>".$eventVal['EventName']."</option>";
                  }
                ?>
              </select>

              <div class="mt-3 mx-auto">
                Select images (Max 400KB): <input type="file" name="files[]" multiple>
              </div>
              <input type="submit" name="submit" class="mt-3">
            </form>
           </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">

            </div>
            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addImage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Add New Event</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id='exitButton'>
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="form-group">
                <label for="eventName">Event Name</label>
                <input type="text" class="form-control" id="eventName">
              </div>
              <div class="mt-3 mx-auto">
                Select images (Max 400KB): <input type="file" name="images[]" id="images" multiple>
              </div>
              <div class="alert alert-danger mt-3 text-center" id="newEventErrShow" style="display: none;" role="alert">
                <span id="newEventErrMsg"></span>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeAdd">Close</button>
            <button type="button" class="btn btn-primary" name="addImage" id="addImageButton">Save changes</button>
          </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <!-- <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script> -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script type="text/javascript">

    function getPosition(event){
      let images = []
      let check = 0;
       var rect = canvas.getBoundingClientRect();
       var x = event.clientX - rect.left;
       var y = event.clientY - rect.top;
       let xVal = 0;
       let yVal = 0;
       test.forEach(data => {
         xVal = Number(data.xVal);
         yVal = Number(data.yVal);

         if(xVal >= 271 && yVal <= 427){
           if(x >= 400 && x <= 470 && (y >= yVal - 109) &&  (y <= yVal - 39)){
             check = 1;
             showImages(data.images);
             $('#exampleModalLong').modal('show');
           }
         }

         if(xVal < 271 && yVal <= 427){
           if(x >= 40 && x <= 110 && (y >= yVal - 109) &&  (y <= yVal - 39)){
             check = 1;
             showImages(data.images);
             $('#exampleModalLong').modal('show');

           }
         }

         if(xVal >= 271 && yVal > 427){
           if(x >= 400 && x <= 470 && (y >= yVal - 13) &&  (y <= yVal + 56)){
             check = 1;
             showImages(data.images);
             $('#exampleModalLong').modal('show');
           }
         }

         if(xVal < 271 && yVal > 427){
           if(x >= 40 && x <= 110 && (y >= yVal - 13) && (y <= yVal + 55)){
             check = 1;
             showImages(data.images);
             $('#exampleModalLong').modal('show');
           }
         }

       })

        if(!check){
          $('#addImage').modal('show');
          $('#closeAdd').val(x.toFixed(2));
          $('#exitButton').val(y.toFixed(2));
        }


    }

    function showImages(images){
      $( ".carousel-inner" ).empty();
      images.forEach((data, idx) => {
        if(idx === 0){
          $( ".carousel-inner" ).append("<div class='carousel-item active'><img src='./Assets/Images/" + data.fileName + "' class='d-block w-100'> </div>");

        }

        if(idx !== 0){
          $( ".carousel-inner" ).append("<div class='carousel-item'><img src='./Assets/Images/" + data.fileName + "' class='d-block w-100'> </div>");
        }
      })
    }

    function drawCoordinates(x,y,name){
      let xShift = name.length * 2
      console.log(xShift)
      var ctx = document.getElementById("canvas").getContext("2d");

      ctx.fillStyle = "black";

      ctx.beginPath();
      ctx.arc(x, y, 5, 0, Math.PI * 2, true);
      ctx.fill();
      ctx.font = "10px Arial";
      ctx.fillText(`${name}`,x - xShift, y - 10);
    }

    function showFourImages(x,y,imagesO){
      let c = document.getElementById("canvas").getContext("2d");

      let oneX = 0;
      let twoX = 0;
      let threeX = 0;
      let fourX = 0;
      let test = 5;
      let lineX = 410;
      let lineY = y - 49;

      let textY = y - 20;

      let oneY =  y - 89;
      let twoY = y - 99;
      let threeY = y - 109;

      if(x >= 271){
        oneX = 400;
        twoX = 410;
        threeX = 420;
        fourX = 420;
      }

      if(x < 271){
        oneX = 60;
        twoX = 50;
        threeX = 40;
        fourX = 70;
        test = -3;
        lineX = 102;
      }

      if(y > 427){
        oneY = y + 5;
        twoY = y - 5;
        threeY = y - 15;
        textY = y + 75;
        lineY = y + 40;
      }

       let imageOne;
       let imageTwo;
       let imageThree

       if(imagesO.length >= 3){
         imageOne = "./Assets/Images/" + imagesO[0].fileName;
         imageTwo = "./Assets/Images/" + imagesO[1].fileName;
         imageThree = "./Assets/Images/" + imagesO[2].fileName;
         let threeImageArray = {imageOne, imageTwo, imageThree};

         loadImages(threeImageArray, function(images){
           c.globalCompositeOperation = 'source-over';
           c.shadowColor = '#383126';
            c.shadowBlur = 5;
            c.shadowOffsetX = test;
            c.shadowOffsetY = 5;
            c.fill();
            c.font = "15px Arial";
            c.fillText(`+${imagesO.length - 3}`,fourX, textY);
          c.drawImage(images.imageThree, threeX, threeY, 50, 50);
          c.drawImage(images.imageTwo, twoX,twoY, 50, 50);
          c.drawImage(images.imageOne, oneX,oneY, 50, 50);
          c.restore();
         })

       }

       if(imagesO.length < 3){
         let imageOne;
         let imageTwo;
         let object = {};

         if(imagesO.length === 1){
           imageOne = "./Assets/Images/" + imagesO[0].fileName;

           object = {imageOne}

           loadImages(object, function(images){
             c.globalCompositeOperation = 'source-over';
             c.shadowColor = '#383126';
              c.shadowBlur = 5;
              c.shadowOffsetX = test;
              c.shadowOffsetY = 5;
              c.fill();
              c.font = "15px Arial";
              c.fillText(`+0`,fourX, textY);
            c.drawImage(images.imageOne, oneX,oneY, 50, 50);
           })
         }

         if(imagesO.length === 2){
           imageOne = "./Assets/Images/" + imagesO[0].fileName;
           imageTwo = "./Assets/Images/" + imagesO[1].fileName;

           object = {imageOne, imageTwo}

           loadImages(object, function(images){
             c.globalCompositeOperation = 'source-over';
             c.shadowColor = '#383126';
              c.shadowBlur = 5;
              c.shadowOffsetX = test;
              c.shadowOffsetY = 5;
              c.fill();
              c.font = "15px Arial";
              c.fillText(`+0`,fourX, textY);
            c.drawImage(images.imageTwo, twoX,twoY, 50, 50);
            c.drawImage(images.imageOne, oneX,oneY, 50, 50);
           })
         }
       }

       c.shadowColor = '#383126';
        c.shadowBlur = 4;
        c.shadowOffsetX = 4;
        c.shadowOffsetY = 4;
      c.beginPath();
      c.moveTo(x, y);
      c.lineTo(lineX, lineY);
      c.stroke();

    }

    function loadImages(sources, callback) {
      var images = {};
      var loadedImages = 0;
      var numImages = 0;
      // get num of sources
      for(var src in sources) {
          numImages++;
      }
      for(var src in sources) {
          images[src] = new Image();
          images[src].onload = function() {
            if(++loadedImages >= numImages) {
              callback(images);
            }
          };
          images[src].src = sources[src];
      }
    }

    window.onload = function() {

      var canvas = document.getElementById("canvas");
      var c = canvas.getContext("2d");

       var img = document.getElementById("state");

       c.drawImage(img, 10, 10);
       c.beginPath();

      test.forEach(data => {
        console.log(data);
        drawCoordinates(Number(data.xVal), Number(data.yVal), data.EventName);
        showFourImages(Number(data.xVal), Number(data.yVal), data.images, canvas);
      })

       $("#canvas").click(function(e){
          getPosition(e);

        });

        $('#addImageButton').click(function(e){
          let x = $('#closeAdd').val();
          let y = $('#exitButton').val();
          let eventName = $('#eventName').val();
          let images = $('#images')[0].files;
          let imageArray = {};

          let filesTest = new FormData();
          filesTest.append('eventName', eventName);
          filesTest.append('xVal', x);
          filesTest.append('yVal', y);
          filesTest.append('numberFiles', images.length);
          for(let i = 0; i < images.length; i++){
            filesTest.append(`${i}`, images[i]);
          }

          $.ajax({
              type: 'post',
              url: 'AddNewEvent.php',
              processData: false,
              contentType: false,
              data: filesTest,
              success: function (response) {
                if(response.includes('Success')){
                  $('#addImage').modal('hide');
                  location.reload();
                }

                if(!response.includes('Success')){
                  $('#newEventErrMsg').text(response);
                  $('#newEventErrShow').show();
                }
              },
              error: function (err) {
                $('#newEventErrMsg').text('Request Entity Too Large');
                $('#newEventErrShow').show();
              }
          });
        })
    }

    </script>
  </body>
</html>
