<?php
  # Author: Adolfo Moreno
  # Description: Stock watchlist
  
  # Mysql Connection Variables.
  $servername = "127.0.0.1:3306";
  $username = "root";
  $password = "password";
  $dbname = "myDB";
?>
<?php
  # Create connection.
  $conn = mysqli_connect($servername, $username, $password);
  # Check connection.
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }
  # Create database.
  $sql = "CREATE DATABASE myDB";
  if (mysqli_query($conn, $sql)) {
    echo "Database created successfully";
  } else {
    # echo "Error creating database: " . mysqli_error($conn);
  }
  # Close our connection
  mysqli_close($conn);
?>
<?php
  # Create userStock table if not already created.
  # Create connection.
  $conn = new mysqli($servername, $username, $password, $dbname);
  # Check connection.
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  # sql to create table with 1 column
  $sql = "CREATE TABLE IF NOT EXISTS `userStock` (
    `symbol` VARCHAR(18) NOT NULL,
    PRIMARY KEY (`symbol`)
  )";

  # Check that we actually created table.
  if ($conn->query($sql) === TRUE) {
    #echo "Table userStock created successfully";
  } else {
    #echo "Error creating table: " . $conn->error;
  }
  #safely close our connection.
  $conn->close();
?>
<?php
 // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
 // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $nameErr = ""; # Hold our error code.
  $name = ""; # Hold our symbol.

 # wait until button is press then process data into database.
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    # Check that textfield is not empty.
    if (empty($_POST["name"])) {
      $nameErr = "Symbol is required";
    } else {
      # grab our symbol from HTML page.
      $name = test_input($_POST["name"]);
      # Convert Symbol to uppercase to check sql db.
      $name = strtoupper($name);
      # Query our quotes DB to make sure that symbol is available.
      $sql = "SELECT 1 FROM quotes WHERE symbol = '" . $name . "'";
      $result = $conn->query($sql);
      # if Symbol available Query userStock to make sure its not already there.
      if ($result->num_rows > 0) {
        # echo "Found";
        $sql = "SELECT 1 FROM userStock WHERE symbol = '" . $name . "'";
        $result = $conn->query($sql);
        # If already in watchlist alert the user that its already there.
        if ($result->num_rows > 0) {
          echo "<script language='javascript'>alert('Error: This symbol has already been added to the watchlist.');</script>";;
        } else {
          # if not in the watchlist add to database table.
          $sql = "INSERT INTO userStock(symbol) VALUES(" . "'" . $name . "'" . " )";
          $result = $conn->query($sql);
        }
      } else{
        # if not in quotes table alert the user that the symbol does not exist.
       echo "<script language='javascript'>alert('Error: The given symbol does not exist.');</script>";
      }
      # Safely close our connection.
      $conn->close();
      # make sure only letters used.
      if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
        $nameErr = "Only letters and white space allowed";
      }
    }
  }

# Test correct input
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

?>
<?php
 # Stay alert to see if user wants to delete item from watchlist.
  $conn = new mysqli($servername, $username, $password, $dbname);
  # Check connection.
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  # If we passed a symbol to be deleted perform removal query.
  if(isset($_GET['del'])){
    $symbol = $_GET['del'];
    $removeQuery = "DELETE FROM userStock WHERE symbol = '{$symbol}'";
    $result = $conn->query($removeQuery);
    # make sure query got executed.
    if($result) {
      #echo "Done";
    } else{
      #echo "fail";
    }
  }
  # safely close our connection.
  $conn->close();
?>

<html>
  <head>
    <!--  Import our javascript file-->
    <script type="text/javascript" src="mainJS.js"></script>
  </head>
    <style>
      table {
        border-collapse: collapse;
        width: 80%;
      }
      th, td {
        text-align: left;
        padding: 8px;
      }
      tr:nth-child(even){background-color: #f2f2f2}
      th {
        background-color: #000000;
        color: white;
      }

      input[type = text]{
        border-radius: 4px;
        height: 35px;
        font-size: 14px;
      }

      input[type = submit]{
        background: #000000; /* Green */
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        border-radius: 4px;
      }

      a{
        text-decoration:none;
      }
      a.underlined{
        text-decoration:underline;
      }
    </style>
    <body>
      <center>
        <h1 style="font-size:300%;">User Stock Watchlist</h1>
        <!-- Create our form and set to post to PHP for varification  -->
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <p style="font-size:160%;"><b>Symbol: </b><input type="text" name="name" placeholder="Enter Symbol" value="<?php echo $name;?>">
            <!-- Check for error -->
            <span class="error">*<?php echo $nameErr;?></span>
            <input type="submit" name="submit" value="Add Symbol">
          </form>
        </p>
          <?php
          # Populate our watchlist table with our elements from userStock table
            $conn = new mysqli($servername, $username, $password, $dbname);
            # Check connection
            if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
            }
            # Reduce redundancy by joining two tables using symbols as key
            $sql = "SELECT * FROM quotes JOIN userStock ON quotes.symbol = userStock.symbol";
            $result = $conn->query($sql);
            # Display table
            if ($result->num_rows > 0) {
              echo "<table id=\"myStocks\"><tr>";
              echo "
              <th onclick=\"sortTable(0)\">Symbol</th>
              <th onclick=\"sortTable(1)\">Name</th>
              <th onclick =\"sortTable(2)\"> Last</th>
              <th onclick =\"sortTable(3)\"> Change</th>
              <th onclick =\"sortTable(4)\"> % Change</th>
              <th onclick =\"sortTable(5)\"> Volume</th>
              <th onclick =\"sortTable(6)\"> Trade Time</th>
              <th> Delete</th>
              ";

              # output data for each row
              # want to pass symbol for delete button since we want to keep track
              # of what element to delete
              while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["symbol"]. "</td><td>" . $row["name"]. "</td><td>"
                . $row["last"]. "</td><td>" .$row["change"]. "</td><td>". $row["pctchange"].
                "</td><td>" .$row["volume"]. "</td><td>". $row["tradetime"]."</td>
                <td><a href='index.php?del=".$row['symbol']."' class='btn btn btn-danger'
                aria-label='Left Align' name='remove' value='remove'>X</button></td></tr>";
              }
              echo "</table>";
            } else {
              echo "<h1>There are no symbols in your watchlist, please add one.<h1>";
            }
            $conn->close();
          ?>
        </center>
      </body>
</html>
