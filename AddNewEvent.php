<?php
  require_once "dbconnect.php";
  $numberFiles = $_POST['numberFiles'];
  if (isset($_FILES) && !empty($_FILES) && isset($_POST['eventName'])) {
    $today = date("Y/m/d");
    $eventName = $_POST['eventName'];
    $xVal = $_POST['xVal'];
    $yVal = $_POST['yVal'];

    $eventID = '';

    $upload_dir = './Assets/Images/';
    $allowed_types = array('jpg', 'png', 'jpeg', 'gif');

    if($eventName === ""){
      print "No Name Provided";
    }
    
    if($eventName !== ""){
      $sqlEvent = "INSERT INTO `pj-events` (Name, xVal, yVal) VALUES ('{$eventName}', {$xVal}, ${yVal})";
      $sqlEvent = $DB->Execute($sqlEvent);

      $findEvent = "SELECT EID FROM `pj-events` WHERE Name = '{$eventName}'";
      $findEvent = $DB->Execute($findEvent);

      foreach ($findEvent as $key => $value) {
        $eventID = $value['EID'];
      }

      for($i = 0; $i < $numberFiles; $i++){
        $file_tmpname = $_FILES[$i]['tmp_name'];
        $file_name = $_FILES[$i]['name'];
        $file_size = $_FILES[$i]['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Set upload file path
        $filepath = $upload_dir.$file_name;

        // Check file type is allowed or not
        if(in_array(strtolower($file_ext), $allowed_types)) {

            if(file_exists($filepath)) {
                $fileName = time().$file_name;
                $filepath = $upload_dir.$fileName;

                if( move_uploaded_file($file_tmpname, $filepath)) {
                    $insertImage = "INSERT INTO `pj-images` (EID, FileName, Date) VALUES ({$eventID}, '{$fileName}', '{$today}')";

                    try{
                      $insertImage = $DB->Execute($insertImage);
                      print "Success";
                    }catch(Exception $e){
                      print $e;
                    }
                }
                else {
                    echo "Error uploading {$file_name}";
                }
            }
            else {

                if( move_uploaded_file($file_tmpname, $filepath)) {
                  $insertImage = "INSERT INTO `pj-images` (EID, FileName, Date) VALUES ({$eventID}, '{$file_name}', '{$today}')";

                  try{
                    $insertImage = $DB->Execute($insertImage);
                    print "Success";
                  }catch(Exception $e){
                    print $e;
                  }
                }
                else {
                    echo "Error uploading {$file_name}";
                }
            }
        }
        else {

            // If file extention not valid
            echo "Error uploading {$file_name} ";
            echo "({$file_ext} file type is not allowed)";
        }
      }
    }

  }

  if (!$numberFiles) {
    print "Missing Input";
  }
?>
