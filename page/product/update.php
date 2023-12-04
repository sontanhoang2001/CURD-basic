<?php
$outsideFolder = '../../';

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ${outsideFolder}login.php");
    exit;
}

// Include config file
require_once "../../config.php";

// Define variables and initialize with empty values
$name = $address = $salary = $startDate = $endTime = "";
$role = 0;
$name_err = $address_err = $salary_err = $role_err = $startDate_err = $endTime_err = "";

// Processing form data when form is submitted
if (isset($_POST["id"]) && !empty($_POST["id"])) {
    // Get hidden input value
    $id = $_POST["id"];

    // Validate name
    $input_name = trim($_POST["name"]);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
        $name_err = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate address address
    $input_address = trim($_POST["address"]);
    if (empty($input_address)) {
        $address_err = "Please enter an address.";
    } else {
        $address = $input_address;
    }

    // Validate salary
    $input_salary = trim($_POST["salary"]);
    if (empty($input_salary)) {
        $salary_err = "Please enter the salary amount.";
    } elseif (!ctype_digit($input_salary)) {
        $salary_err = "Please enter a positive integer value.";
    } else {
        $salary = $input_salary;
    }


    // Validate startDate
    $input_startDate = trim($_POST["startDate"]);
    if (empty($input_startDate)) {
        $startDate_err = "Please enter the startDate.";
    } else {
        $startDate = $input_startDate;
    }

    // Validate endTime
    $input_endTime = trim($_POST["endTime"]);
    if (empty($input_endTime)) {
        $endTime_err = "Please enter the endTime.";
    } else {
        $endTime = $input_endTime;
    }

    // Validate role
    $input_role = trim($_POST["role"]);
    if ($input_role == 0) {
        $role_err = "Vui lòng nhập role";
    } elseif (!ctype_digit($input_role)) {
        $role_err = "Vui lòng nhập đúng định dạng.";
    } else {
        $role = $input_role;
    }


    // Check input errors before inserting in database
    if (empty($name_err) && empty($address_err) && empty($salary_err) && empty($role_err) && empty($startDate_err) && empty($endTime_err)) {
        // Prepare an update statement
        $sql = "UPDATE employees SET name=?, address=?, salary=?, startDate=?, endTime=?, roleId=? WHERE id=?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssii", $param_name, $param_address, $param_salary, $param_startDate, $param_endTime, $param_role, $param_id);

            // Set parameters
            $param_name = $name;
            $param_address = $address;
            $param_salary = $salary;
            $param_startDate = $startDate;
            $param_endTime = $endTime;

            $param_role = $role;
            $param_id = $id;


            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Records updated successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // // Close connection
    // mysqli_close($link);
} else {
    // Check existence of id parameter before processing further
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        // Get URL parameter
        $id =  trim($_GET["id"]);

        // Prepare a select statement
        $sql = "SELECT employees.* FROM employees INNER JOIN roles ON roles.id=employees.roleId WHERE employees.id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Set parameters
            $param_id = $id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Retrieve individual field value
                    $name = $row["name"];
                    $address = $row["address"];
                    $salary = $row["salary"];
                    $roleId = $row["roleId"];

                    $dateTime1 = new DateTime($row['startDate']);
                    $startDate = $dateTime1->format('Y-m-d');

                    // Assuming $startDate contains your datetime value
                    $endTime = date('Y-m-d\TH:i', strtotime($row['endTime']));
                } else {
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);

    } else {
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Update Record</h2>
                    <p>Please edit the input values and submit to update the employee record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="text" name="salary" class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary; ?>">
                            <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                        </div>

                        <div class="form-group">
                            <label>Start date</label>
                            <input class="form-control" width="276" type="date" name="startDate" value="<?php echo $startDate; ?>" <?php echo (!empty($startDate_err)) ? 'is-invalid' : ''; ?> />
                            <span class="invalid-feedback"><?php echo $startDate_err; ?></span>
                        </div>


                        <div class="form-group">
                            <label>End time</label>
                            <input class="form-control <?php echo (!empty($endTime_err)) ? 'is-invalid' : ''; ?>" type="datetime-local" name="endTime" value="<?php echo $endTime; ?>" />
                            <span class="invalid-feedback"><?php echo $endTime_err; ?></span>
                        </div>


                        <?php
                        // echo "roleId: " . $roleId;
                        // Attempt select query execution
                        $sql = "SELECT * FROM roles";
                        if ($result = mysqli_query($link, $sql)) {
                            if (mysqli_num_rows($result) > 0) {
                        ?>
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Role</label>
                                    <select class="form-control" id="exampleFormControlSelect1" name='role'>
                                        <option value=0>Chọn role</option>

                                        <?php
                                        while ($row = mysqli_fetch_array($result)) { ?>
                                            <option value='<?php echo $row['id']; ?>' <?php if ($roleId == $row['id']) {
                                                                                            echo "selected";
                                                                                        } ?>>
                                                <?php echo $row['name']; ?>
                                            </option> <?php
                                                    }
                                                        ?>
                                    </select>
                                    <span class="invalid-feedback" style="<?php echo (!empty($role_err)) ? 'display:block !important;' : ''; ?>"><?php echo $role_err; ?></span>
                                </div>

                        <?php
                                // Free result set
                                mysqli_free_result($result);
                            }
                        }


                        // Close connection
                        mysqli_close($link);
                        ?>


                        <input type="hidden" name="id" value="<?php echo $id; ?>" />
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>