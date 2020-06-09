<?php

session_start();


include '../classes/db.php';

function activity_log($log, $type){
    $conn = new Db;
    $conn = $conn->connect();

    $stmt = mysqli_stmt_init($conn);
    $sql = "INSERT INTO bc_logs (log_activity, type) VALUES(?,?)";

    if(mysqli_stmt_prepare($stmt, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $log, $type);
        mysqli_stmt_execute($stmt);
    }
}

function check_password($password){
    if(isset($_SESSION['me'])){
        $user = $_SESSION['me'];
        $conn = new Db;
        $conn=$conn->connect();
        $stmt = mysqli_stmt_init($conn);
        $sql = "SELECT * FROM bc_members WHERE id = ?;";

        if(mysqli_stmt_prepare($stmt, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $user);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = $result->fetch_assoc();

            if(password_verify($password, $row['password'])){
                return true;
            }
        
    }
    return false;

}
}

if(isset($_POST['login_username'])){
    session_unset();

    $username = $_POST['login_username'];
    $password = $_POST['password'];

    if(empty($username) || empty($password)){
        exit();
    }else{
        $conn = new Db;
        $conn = $conn->connect();

        $stmt = mysqli_stmt_init($conn);
        $sql = "SELECT * FROM bc_members WHERE username = ?";
        if(mysqli_stmt_prepare($stmt, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($result->num_rows < 1){
                exit("no user");
            }else{
                $row = $result->fetch_assoc();
                $id = $row['id'];

                if(password_verify($password, $row['password'])){
                    $_SESSION['me'] = $id;
                    $_SESSION['me_username'] = $username;
                    $_SESSION['me_name'] = $row['name'];
                    $_SESSION['me_role'] = $row['role'];
                    $_SESSION['me_amount'] = $row['amount'];
    
                    echo "logged in";
                }else{
                    exit("wrong password");
                }
            }
        }else{
            exit("failed to prepare");
        }
    }
}

else if(isset($_POST['new_member'])){
    $me = $_SESSION['me'];
    $me_name = $_SESSION['me_name'];

    $conn = new Db;
    $conn = $conn->connect();

    $name = $_POST['new_member'];
    if(empty($name)){
        exit("empty name");
    }
    else{
        $sql = "SELECT * FROM bc_members WHERE name = ?";
        $stmt = mysqli_stmt_init($conn); 

        if(mysqli_stmt_prepare($stmt, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if($result->num_rows > 0){
                exit("name exists");
            }
            
            else{
                $sql = "INSERT INTO bc_members (name, password) VALUES(?, ?)";
                $stmt = mysqli_stmt_init($conn);
                if(mysqli_stmt_prepare($stmt, $sql)){
                    $password = "000000";
                    $pwd = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, "ss", $name, $pwd);
                    mysqli_stmt_execute($stmt);
                    if($stmt->affected_rows == 1){
                        activity_log("Member [$name] Added By $me_name", "registration");
                        echo 'registered';
                    }
                }else{
                    exit("failed to prepare");
                }
            }
        }else{
            exit("failed to prepare");
        }
    }
}

else if(isset($_POST['get_members'])){
    $conn = new Db;
    $conn = $conn->connect();

    $stmt = mysqli_stmt_init($conn);
    $sql = "SELECT * FROM bc_members";

    if(mysqli_stmt_prepare($stmt, $sql)){   
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if($result->num_rows > 0){
            Class members{
                public $member;
                public $names;
                public $payments;
                public $roles;

                function __construct()
                {
                    $this->member=[];
                    $this->names=[];
                    $this->payments=[];
                    $this->roles = [];
                }
            }

            $members = new members;
            while($row = $result->fetch_assoc()){
                array_push($members->names, $row['name']);
                array_push($members->payments, $row['amount']);
                array_push($members->roles, $row['role']);
                array_push($members->member, $row['id']);
            }

            echo json_encode($members);
        }
    }
    else{
        exit("prepare");
    }
}

else if(isset($_POST['asign_role'])){
    $role = $_POST['asign_role'];
    $member = $_POST['member'];
    $password = $_POST['password'];

    if(empty($role) || empty($member) || empty($password)){
        exit();
    }
    else{
        $user = $_SESSION['me'];
        $conn = new Db;
        $conn=$conn->connect();
        $stmt = mysqli_stmt_init($conn);
        $sql = "SELECT * FROM bc_members WHERE id = ?;";

        if(mysqli_stmt_prepare($stmt, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $user);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $row = $result->fetch_assoc();

            if(password_verify($password, $row['password'])){
                $stmt = mysqli_stmt_init($conn);
                $sql = "UPDATE bc_members SET role=? WHERE id = ?";
                if(mysqli_stmt_prepare($stmt, $sql)){
                    mysqli_stmt_bind_param($stmt, "ss", $role, $member);
                    mysqli_stmt_execute($stmt);
                    if($stmt->affected_rows == 1){
                        $asignee_role = $row['role'];
                        $asignee_name = $row['name'];
                        activity_log("Role [$role] assigned to member [$member] by $asignee_role $asignee_name", "role");
                        exit('Role Asigned');
                    }
                }
            }
            else{
                exit("Wrong Password");
            }
        
    }
    }
}

else if(isset($_POST['amount'])){
    $amount = $_POST['amount'];
    $password = $_POST['password'];
    $member = $_POST['member'];
    if(empty($amount) || empty($password)){
        exit();
    }
    else{
        $user = $_SESSION['me'];
        $conn = new Db;
        $conn=$conn->connect();
        $stmt = mysqli_stmt_init($conn);
        $sql = "SELECT * FROM bc_members WHERE id = ?;";
    
        if(mysqli_stmt_prepare($stmt, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $user);
            mysqli_stmt_execute($stmt);
        
            $result = mysqli_stmt_get_result($stmt);
            $row = $result->fetch_assoc();
        
            if($row['role'] !== "secretary"){
                exit("Only Secretaries Can Mark Contributions");
            }
            else if($row['role'] == "secretary"){
                if(password_verify($password, $row['password'])){
                    $asigner_name = $row['name'];
                    $asigner_id = $row['id'];
                    $asigner_role = $row['role'];

                    $stmt = mysqli_stmt_init($conn);
                    $sql = "SELECT * FROM bc_members WHERE id = ?";
                    if(mysqli_stmt_prepare($stmt, $sql)){
                        mysqli_stmt_bind_param($stmt, "s", $member);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if($result->num_rows == 1){
                            $row = $result->fetch_assoc();
                            $new_amount = $amount+$row['amount'];
                            $asignee_id = $row['id'];
                            $asignee_name = $row['name'];

                            $stmt = mysqli_stmt_init($conn);
                            $sql = "UPDATE bc_members SET amount = ? WHERE id = ?";

                            if(mysqli_stmt_prepare($stmt, $sql)){
                                mysqli_stmt_bind_param($stmt, "ss", $new_amount, $member);
                                mysqli_stmt_execute($stmt);
                                if($stmt->affected_rows == 1){
                                    activity_log("Contribution of ksh $amount added by $asigner_role $asigner_name [$asigner_id] for $asignee_name [$asignee_id]", "contribution");
                                    exit("Contribution Added");
                                }
                            }
                        }
                    }
                }
                else{
                    exit ("Wrong Password");
                }
            }
        
        }
    }
}

else if(isset($_POST['logout'])){
    session_unset();
    session_destroy();
}

else{
    echo "failed";
}