<html>

<head>
    <title>FitnessTracker</title>
</head>

<style>
    body {
        font-family: 'Arial', Helvetica, sans-serif;
    }

    #heading {
        text-align: center;
        color: royalblue;
    }

    .name {
        text-align: end;
        margin-right: 2rem;
    }

    .statistic {
        margin: 4rem;
        border: .3rem solid lightskyblue;
        display: grid;
        grid-template-columns: 50% 50%;
        border-radius: .2rem;
    }

    .statistic-header {
        grid-column: span 2;
        text-align: center;
    }

    .gymSession {
        margin: 2rem;
        padding: 1rem;
        border: .15rem solid lightskyblue;
    }

    .foodLog {
        margin: 2rem;
        padding: 1rem;
        border: .15rem solid lightskyblue;
    }

    .foodLog-log {
        display: grid;
        grid-template-columns: 30% 20% 30% 20%;
    }

    .nameVal {
        display: inline-block;
        margin-left: .3rem;
        font-weight: 900;
    }

    .btnStyle {
        padding: .5em;
        color: royalblue;

    }

    .groups {
        text-align: center;
        border: .3rem solid lightgreen;
        margin: 4rem;
        border-radius: .2rem;
    }

    .group {
        padding: 1rem;
    }

    .groupVal {
        display: inline-block;
        padding-left: 2rem;
    }

    .gymSessionLocations {
        text-align: center;
        display: grid;
        grid-template-columns: 50% 50%;
    }

    .selectData {
        text-align: center;
        display: grid;
        grid-template-columns: 50% 50%;
    }
</style>



<?php
$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = 'NULL'; // edit the login credentials in connectToDB()
$show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())
$person_id = '16b23a2k4b';
$stat_id = '3a2g8s52g4';
$log_id = '1a723dys73';
$log_values;
$group_values;
$divisionResponse;

//SETUP FUNCTIONS ------------------------------------------------------------------
function debugAlertMessage($message)
{
    global $show_debug_alert_messages;

    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alerpt('" . $message . "');</script>";
    }
}

function executePlainSQL($cmdstr)
{ //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;

    $statement = OCIParse($db_conn, $cmdstr);
    //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
        echo htmlentities($e['message']);
        $success = False;
    }

    return $statement;
}

function executeBoundSQL($cmdstr, $list)
{
    /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
   In this case you don't need to create the statement several times. Bound variables cause a statement to only be
   parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
   See the sample code below for how this function is used */

    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            //echo $val;
            //echo "<br>".$bind."<br>";
            OCIBindByName($statement, $bind, $val);
            unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }
}

function connectToDB()
{
    global $db_conn;

    // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
    // ora_platypus is the username and a12345678 is the password.
    $db_conn = OCILogon("ora_alexma12", "a61926309", "dbhost.students.cs.ubc.ca:1522/stu");

    if ($db_conn) {
        debugAlertMessage("Database is Connected");
        return true;
    } else {
        debugAlertMessage("Cannot connect to Database");
        $e = OCI_Error(); // For OCILogon errors pass no handle
        echo htmlentities($e['message']);
        return false;
    }
}

function disconnectFromDB()
{
    global $db_conn;

    debugAlertMessage("Disconnect from Database");
    OCILogoff($db_conn);
}


//POPULATE TABLES + SETUP INTERFACE -------------------------------------
function fetchUserInfo($person_id)
{

    $result = executePlainSQL("SELECT name FROM Person WHERE id = '" . $person_id . "'");
    if (($row = oci_fetch_row($result)) != false) {
        echo "<div class = 'name' > Welcome, <span class = 'nameVal'> " . $row[0] . "</span></div>";
    }
}

function fetchGroupInfo($person_id)
{
    $result = executePlainSQL("SELECT * FROM GroupedPerson gp, ExerciseGroup eg WHERE eg.group_id = gp.group_id AND gp.person_id = '" . $person_id . "'");
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<div class = 'group'> <span class = 'groupVal'> Name: <strong>" . $row["GROUP_NAME"] . "</strong></span><span class = 'groupVal'>Group ID: <strong>"  . $row["GROUP_ID"] . "</strong></span></div>";
    }
}

//QUERIES -----------------------------------------------
//INSERT: insert a new food to FoodItem
function handleInsertRequest()
{
    global $db_conn;
    //Getting the values from user and insert data into the table
    $tuple = array(
        ":bind1" => $_POST['logID'],
        ":bind2" => $_POST['foodName'],
        ":bind3" => $_POST['calories'],
        ":bind4" => $_POST['time']

    );

    $alltuples = array(
        $tuple
    );

    executeBoundSQL("insert into FoodLog_Log values (:bind1, :bind2, :bind3, :bind4)", $alltuples);
    OCICommit($db_conn);
}

//DELETE: delete a food from FoodItem
function handleDeleteRequest()
{
    global $db_conn;
    $delete_name = $_POST['food_name'];
    $delete_cal = $_POST['calories'];

    $result = executePlainSQL("DELETE FROM FoodItem WHERE food_name= '$delete_name' AND calories= $delete_cal");
    OCICommit($db_conn);
}

//UPDATE: update Group name
function handleUpdateRequest()
{
    global $db_conn;

    $newName = $_POST['newName'];
    $group_id = $_POST['group_id'];

    // you need the wrap the old name and new name values with single quotations
    executePlainSQL("UPDATE ExerciseGroup SET group_name='" . $newName . "'WHERE group_id ='" .  $group_id . "'");
    OCICommit($db_conn);
}

//SELECTION
function handleSelectRequest()
{
    global $selectedQueryResult;
    global $attributeOne;
    global $attributeTwo;
    $table = $_GET['Table'];
    $attributeOne = $_GET['attributeOne'];
    $attributeTwo = $_GET['attributeTwo'];
    $conditionOne = $_GET['conditionOne'];
    $conditionTwo = $_GET['conditionTwo'];
    $valueOne = is_numeric($_GET['valueOne']) ? $_GET['valueOne'] : "'" . $_GET['valueOne'] . "'";
    $valueTwo = is_numeric($_GET['valueTwo']) ? $_GET['valueTwo'] : "'" . $_GET['valueTwo'] . "'";


    $selectQuery = "SELECT " . $attributeOne;

    if ($attributeTwo != "") {
        $selectQuery .= ", " . $attributeTwo;
    }

    $selectQuery .= " FROM " . $table;

    if ($conditionOne != "None") {
        $queryConditionOne = "$attributeOne $conditionOne $valueOne";
    }

    if ($conditionTwo != "None") {
        $queryConditionTwo = "$attributeTwo $conditionTwo $valueTwo";
    }

    if ($conditionOne != "None") {
        if ($conditionTwo != "None") {
            $selectQuery .= " WHERE " . $queryConditionOne . " AND " . $queryConditionTwo;
        } else {
            $selectQuery .= " WHERE " . $queryConditionOne;
        }
    }

    if ($conditionTwo != "None" && $conditionOne == "None") {
        $selectQuery .= " WHERE " . $queryConditionTwo;
    }

    $result = executePlainSQL($selectQuery);
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $selectedQueryResult .= "<span>" . $row[0]  . "</span>    <span>" . $row[1] . "</span>";
    }
}

//PROJECTION: project selected attributes from StrengthGoal
function handleProjectionRequest()
{
    global $db_conn;
    global $projectionResult;
    global $person_id;

    $projQuery = "";
    $pid = false;
    $exerciseName = false;
    $sWeight = false;
    $sSets = false;
    $sReps = false;
    $gWeight = false;
    $gSets = false;
    $gReps = false;

    // if(isset($_GET['gid'])){ $projQuery .= 'goal_id';}
    if (isset($_GET['pid'])) {
        $projQuery .= ", person_id";
        $pid = true;
    }
    if (isset($_GET['exercise_name'])) {
        $projQuery .= ", e_name";
        $exerciseName = true;
    }
    if (isset($_GET['s_weight'])) {
        $projQuery .= ", start_weight";
        $sWeight = true;
    }
    if (isset($_GET['s_sets'])) {
        $projQuery .= ", start_sets";
        $sSets = true;
    }
    if (isset($_GET['s_reps'])) {
        $projQuery .= ", start_reps";
        $sReps = true;
    }
    if (isset($_GET['g_weight'])) {
        $projQuery .= ", goal_weight";
        $gWeight = true;
    }
    if (isset($_GET['g_sets'])) {
        $projQuery .= ", goal_sets";
        $gSets = true;
    }
    if (isset($_GET['g_reps'])) {
        $projQuery .= ", goal_reps";
        $gReps = true;
    }
    $result = executePlainSQL("SELECT goal_id" . $projQuery . " FROM StrengthGoal WHERE person_id = '$person_id'");
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        // $projectionResult .= "<div class = 'foodLog-log'><span>" . $row["GOAL_ID"] . "</span><span>" . $row["PERSON_ID"] . "</span><span>" . $row["E_NAME"] . "</span><span>" . $row["S_WEIGHT"] . "</span><span>" . $row["S_SETS"] . "</span></div>" . $row["S_REPS"] . "</span></div>" . $row["G_WEIGHT"] . "</span></div>" . $row["G_SETS"] . "</span></div>" . $row["G_REPS"] . "</span></div>";
        $currResult = "<div> <strong> Goal ID: </strong>" .  $row['GOAL_ID'] . "</div>";
        if ($pid) {
            $currResult .= "<div> <strong> Person ID: </strong>" . $row["PERSON_ID"] . "</div>";
        }
        if ($exerciseName) {
            $currResult .= "<div> <strong> Exercise Name: </strong>" . $row["E_NAME"] . "</div>";
        }
        if ($sWeight) {
            $currResult .= "<div> <strong> Starting Weight: </strong>" . $row["START_WEIGHT"] . "</div>";
        }
        if ($sSets) {
            $currResult .= "<div> <strong> Starting Sets: </strong>"  . $row["START_SETS"] . "</div>";
        }
        if ($sReps) {
            $currResult .= "<div> <strong> Starting Reps: </strong>"  . $row["START_REPS"] . "</div>";
        }
        if ($gWeight) {
            $currResult .= "<div> <strong> Goal Weight: </strong>" . $row["GOAL_WEIGHT"] . "</div>";
        }
        if ($gSets) {
            $currResult .= "<div> <strong> Goal Sets: </strong>"  . $row["GOAL_SETS"] . "</div>";
        }
        if ($gReps) {
            $currResult .= "<div> <strong> Goal Reps: </strong>" . $row["GOAL_REPS"] . "</div>";
        }
        $currResult .= "<hr/>";
        $projectionResult .= $currResult;
    }

    OCICommit($db_conn);
}

//JOIN: join FoodLog and FoodItem
function handleJoinRequest()
{

    global $log_id;
    global $log_values;

    $log_date = $_GET['log_date'];
    $result = executePlainSQL(
        "SELECT * 
               FROM FoodLog fl, FoodLog_Log fll, FoodItem fi 
               WHERE fl.log_date ='" . $log_date . "'" .
            "AND fl.log_id = fll.log_id 
                AND fi.food_name = fll.food_name 
                AND fi.calories = fll.calories 
                AND fl.log_id =" . "'" . $log_id . "'"
    );

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $log_id = $row["LOG_ID"];
        $log_values .= "<div class = 'foodLog-log'><span>" . $row["FOOD_NAME"] . "</span><span>" . $row["CALORIES"] . "</span>" . "<span>" . $row["TIME_OF_CONSUMPTION"] . "</span><span>" . ($row["MEAL_TYPE"] ? $row["MEAL_TYPE"] : "Snack") . "</span></div>";
    }
}

//AGGREGTION WITH GROUP BY
function handleGroupByAggregationRequest()
{
    global $groupedExercises;
    $result = executePlainSQL("SELECT e_type, COUNT(*)
                               FROM Exercise 
                               GROUP BY e_type");

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $groupedExercises .= "<span>" . $row[0]  . "</span><span>" . $row[1] . "</span>";
    }
}

//AGGREGATION WITH HAVING
function handleGroupByHaving()
{
    global $havingResult;
    $result = executePlainSQL("SELECT e_name, max(goal_weight) FROM StrengthGoal GROUP BY e_name HAVING COUNT(*) > 1");

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $havingResult .= "<span>" . $row[0]  . "</span><span>" . $row[1] . "</span>";
    }
}

//NESTED AGGREGATION WITH GROUP BY
function handleNestedAggregationRequest()
{
    global $person_id;
    global $nestedAggregationResponse;

    $result = executePlainSQL(
        "SELECT gym_name, Count(*) as count
            FROM GymSession gs1 
            WHERE gs1.session_id 
               IN(SELECT session_id 
                    FROM DailyStatistic ds, GymSession gs2 
                    WHERE ds.person_id = '" . $person_id . "'" . "AND ds.stat_id = gs2.stat_id)
            GROUP BY gym_name
            ORDER BY count DESC"
    );

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $nestedAggregationResponse .= "<span>" . $row[0]  . "</span><span>" . $row[1] . "</span>";
    }
}

//DIVISION: find all people are in all of the groups you are in
function handleDivisionRequest()
{
    global $divisionResponse;
    global $person_id;

    $result = executePlainSQL(
        "SELECT name, id
            FROM Person p 
            WHERE p.id <>'" . $person_id . "'"  .
            "AND NOT EXISTS
               ((SELECT group_id 
                 FROM GroupedPerson gp 
                 WHERE gp.person_id = '" . $person_id . "'"  . ") 
                MINUS
                (SELECT group_id FROM GroupedPerson gp WHERE gp.person_id = p.id))"
    );
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $divisionResponse .= "<div class = 'group'> <span class = 'groupVal'> Name: <strong>" . $row["NAME"] . "</strong></span><span class = 'groupVal'> ID: <strong>"  . $row["ID"] . "</strong></span></div>";;
    }
}


// HANDLE FUNCTIONS --------------------------------------------------------------

function handlePOSTRequest()
{
    if (connectToDB()) {
        if (array_key_exists('updateQueryRequest', $_POST)) {
            handleUpdateRequest();
        } else if (array_key_exists('insertQueryRequest', $_POST)) {
            handleInsertRequest();
        } else if (array_key_exists('deleteQueryRequest', $_POST)) {
            handleDeleteRequest();
        }
        disconnectFromDB();
    }
}

function handleGETRequest()
{
    if (connectToDB()) {
        if (array_key_exists('nestedAggregationQuery', $_GET)) {
            handleNestedAggregationRequest();
        }
        if (array_key_exists('joinQuery', $_GET)) {
            handleJoinRequest();
        }
        if (array_key_exists('divisionQuery', $_GET)) {
            handleDivisionRequest();
        }
        if (array_key_exists('selectQueryRequest', $_GET)) {
            handleSelectRequest();
        }
        if (array_key_exists('groupByAggregationQuery', $_GET)) {
            handleGroupByAggregationRequest();
        }
        if (array_key_exists('projQueryRequest', $_GET)) {
            handleProjectionRequest();
        }
        if (array_key_exists('havingAggregationRequest', $_GET)) {
            handleGroupByHaving();
        }
        disconnectFromDB();
    }
}

if (isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteSubmit'])) {
    handlePOSTRequest();
} else if (isset($_GET['joinRequest']) || isset($_GET['groupBySubmit']) || isset($_GET['nestedAggregationSubmit']) || isset($_GET['divisionSubmit']) || isset($_GET['selectSubmit']) || isset($_GET['projSubmit']) || isset($_GET['havingSubmit'])) {
    handleGETRequest();
}



?>

<body>
    <h1 id="heading">My Fitness Tracker</h1>

    <?php
    $person_id = '16b23a2k4b';
    if (connectToDB()) {
        fetchUserInfo($person_id);
        disconnectFromDB();
    }
    ?>
    <span>

        <div class="groups">
            <h1> My Groups </h1>
            <?php
            $person_id = '16b23a2k4b';
            if (connectToDB()) {
                fetchGroupInfo($person_id);
                disconnectFromDB();
            }
            ?>

            <hr />

            <h4 id="heading">Update Group Name</h4>
            <form method="POST" action="fitness_tracker.php">
                <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
                Group ID: <input type="text" name="group_id"> <br /><br />
                New Group Name: <input type="text" name="newName"> <br /><br />
                <input class="btnStyle" type="submit" value="Update" name="updateSubmit"></p>
            </form>

            <hr />
            <form method="GET" action="fitness_tracker.php">
                <input type="hidden" id="divisionQuery" name="divisionQuery">
                <input class="btnStyle" type="submit" value="Fetch People Who Are In All Of Your Groups" name="divisionSubmit"></p>
            </form>
            <?php
            if (isset($divisionResponse)) {
                echo $divisionResponse;
            }
            ?>
        </div>
        <div class="statistic">
            <h1 class='statistic-header'> Statistics </h1>
            <div class='gymSession'>
                <h2 id='heading'> Data </h2>
                <hr />
                <h4 id='heading'>Select Data </h4>
                <form method="GET" action="fitness_tracker.php">
                    <input type="hidden" id="selectQueryRequest" name="selectQueryRequest">
                    Table: <input type="text" name="Table"> <br /><br />
                    Attribute 1: <input type="text" name="attributeOne">
                    <select name="conditionOne">
                        <option value="None">None</option>
                        <option value="=">Equal to</option>
                        <option value=">">Greater than</option>
                        <option value="<">Less than</option>
                    </select>
                    Value: <input type="text" name="valueOne"> <br /><br />
                    Attribute 2: <input type="text" name="attributeTwo">
                    <select name="conditionTwo">
                        <option value="None">None</option>
                        <option value="=">Equal to</option>
                        <option value=">">Greater than</option>
                        <option value="<">Less than</option>
                    </select>
                    Value: <input type="text" name="valueTwo"> <br /><br />
                    <input class="btnStyle" type="submit" value="Select" name="selectSubmit">
                </form>
                <div class="selectData">
                    <?php
                    if (isset($selectedQueryResult)) {

                        echo "<strong>" . $attributeOne  . "</strong>    <strong>" . $attributeTwo . "</strong>";
                        echo $selectedQueryResult;
                    }
                    ?>
                </div>

                <hr />
                <div>
                    <form method="GET" action="fitness_tracker.php">
                        <input type="hidden" id="nestedAggregationQuery" name="nestedAggregationQuery">
                        <input class="btnStyle" type="submit" value="Fetch The Number Of Times You've Been To Each Gym" name="nestedAggregationSubmit"></p>
                    </form>
                    <div class="gymSessionLocations">
                        <?php
                        if (isset($nestedAggregationResponse)) {
                            echo "<strong> Gym Name </strong><strong> Number Of Times You've Been </strong>";
                            echo $nestedAggregationResponse;
                        }
                        ?>
                    </div>
                </div>

                <hr />
                <h4 id="heading"> Strength Goal Information </h4>
                <form method="GET" action="fitness_tracker.php">
                    <input type="hidden" id="projQueryRequest" name="projQueryRequest">
                    <span>Select the information you want to view:</span><br />
                    <!-- <input type="checkbox" name='gid'> Goal ID <br/> -->
                    <input type="checkbox" name='pid'> Person ID <br />
                    <input type="checkbox" name='exercise_name'> Exercise Name <br />
                    <input type="checkbox" name='s_weight'> Starting Weight <br />
                    <input type="checkbox" name='s_sets'> Starting Sets <br />
                    <input type="checkbox" name='s_reps'> Starting Reps <br />
                    <input type="checkbox" name='g_weight'> Goal Weight <br />
                    <input type="checkbox" name='g_sets'> Goal Sets <br />
                    <input type="checkbox" name='g_reps'> Goal Reps <br />
                    <br />
                    <input class="btnStyle" type="submit" value="Submit" name="projSubmit"></p>
                </form>

                <?php
                if (isset($projectionResult)) {
                    echo "<h3> Your information: </h3><hr/>";
                    echo $projectionResult;
                }
                ?>

                <hr />
                <form method="GET" action="fitness_tracker.php">
                    <input type="hidden" id="havingAggregationRequest" name="havingAggregationRequest">
                    <input class="btnStyle" type="submit" value="Find Highest Goal Weight For Exercises With More Than One Goal Associated With Them" name="havingSubmit"></p>
                </form>
                <div class="selectData">
                    <?php
                    if (isset($havingResult)) {
                        echo "<strong> Exercise Name </strong> <strong> Highest Goal Weight </strong>";
                        echo $havingResult;
                    }
                    ?>
                </div>
                <hr />

                <form method="GET" action="fitness_tracker.php">
                    <input type="hidden" id="groupByAggregationQuery" name="groupByAggregationQuery">
                    <input class="btnStyle" type="submit" value="Find Logged Exercise Type Count" name="groupBySubmit"></p>
                </form>
                <div class="selectData">
                    <?php
                    if (isset($groupedExercises)) {
                        echo "<strong> Exercise Type </strong>  <strong> Count </strong>";
                        echo $groupedExercises;
                    }
                    ?>
                </div>


            </div>

            <div class='foodLog'>

                <h2 id='heading'> Food Log </h2>
                <hr />
                <h4 id='heading'>View Entry</h4>
                <form method="GET" action="fitness_tracker.php">
                    <input type="hidden" id="joinRequest" name="joinRequest">
                    Food Log Date: <input type="text" name="log_date"> <br /><br />
                    <span><input class="btnStyle" type="submit" value="Get The Food Log" name=" joinQuery"></span>
                </form>
                <?php
                if (isset($log_values)) {
                    echo  "<h4> Log Id: " . $log_id . "</h4>";
                    echo  "<div class = 'foodLog-log'> <strong> Name </strong> <strong> Calories </strong> <strong> Time Of Consumption</strong><strong>Meal Type</strong></div>";
                    echo $log_values;
                }
                ?>

                <hr />

                <h4 id='heading'>Add New Entry</h4>
                <form method="POST" action="fitness_tracker.php">
                    <!--refresh page when submitted-->
                    <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
                    Log ID: <input type="text" name="logID"> <br /><br />
                    Food Name: <input type="text" name="foodName"> <br /><br />
                    Calories: <input type="text" name="calories"> <br /><br />
                    Time of Consumption: <input type="text" name="time"> <br /><br />
                    <input class="btnStyle" type="submit" value="Insert" name="insertSubmit"></p>
                </form>


                <hr />

                <h4 id="heading"> Delete Food Items </h4>
                <form method="POST" action="fitness_tracker.php">
                    <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
                    Food Item: <input type="text" name="food_name"> <br /><br />
                    Calories: <input type="text" name="calories"> <br /><br />
                    <input class="btnStyle" type="submit" value="Delete" name="deleteSubmit"></p>
                    <form>

                        <hr />
            </div>
        </div>

</body>

</html>