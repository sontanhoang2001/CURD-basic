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
$name = $address = $salary = $startDate = $endTime = $roleId = "";
$name_err = $address_err = $salary_err = $role_err = $startDate_err = $endTime_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    $input_name = trim($_POST["name"]);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } elseif (!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z\s]+$/")))) {
        $name_err = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate address
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
    $input_role = trim($_POST["roleId"]);
    if ($input_role == 0) {
        $role_err = "Vui lòng nhập role";
    } elseif (!ctype_digit($input_role)) {
        $role_err = "Vui lòng nhập đúng định dạng.";
    } else {
        $roleId = $input_role;
    }


    // Check input errors before inserting in database
    if (empty($name_err) && empty($address_err) && empty($salary_err) && empty($role_err) && empty($startDate_err) && empty($endTime_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO employees (name, address, salary, startDate, endTime, roleId) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_name, $param_address, $param_salary, $param_startDate, $param_endTime, $param_role);

            // Set parameters
            $param_name = $name;
            $param_address = $address;
            $param_salary = $salary;
            $param_startDate = $startDate;
            $param_endTime = $endTime;
            $param_role = $roleId;


            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Records created successfully. Redirect to landing page
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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                    <h2 class="mt-5">Create Record</h2>
                    <p>Please fill this form and submit to add employee record to the database.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                            <input class="form-control" type="date" name="startDate" value="<?php echo $startDate; ?>" />
                            <span class="invalid-feedback" <?php echo (!empty($startDate_err)) ? 'style="display: block;"' : ''; ?>><?php echo $startDate_err; ?></span>
                        </div>

                        <div class="form-group">
                            <label>End time</label>
                            <input class="form-control <?php echo (!empty($endTime_err)) ? 'is-invalid' : ''; ?>" type="datetime-local" name="endTime" value="<?php echo $endTime; ?>" />
                            <span class="invalid-feedback"><?php echo $endTime_err; ?></span>
                        </div>

                        <?php
                        // Attempt select query execution
                        $sql = "SELECT * FROM roles";
                        if ($result = mysqli_query($link, $sql)) {
                            if (mysqli_num_rows($result) > 0) {
                        ?>
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Role</label>
                                    <select class="form-control" id="exampleFormControlSelect1" name='roleId'>
                                        <option value=0>Chọn role</option>
                                        <?php
                                        while ($row = mysqli_fetch_array($result)) { ?>
                                            <option value='<?php echo $row['id']; ?>' <?php if ($roleId == $row['id']) {
                                                                                            echo "selected";
                                                                                        } ?>>
                                                <?php echo $row['name']; ?>
                                            </option>
                                        <?php
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

                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>