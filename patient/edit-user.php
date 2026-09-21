
    <?php

    include("../connection.php");

    if($_POST){
        
        $name=$_POST['name'];
        $nic=$_POST['nic'];
        $oldemail=$_POST["oldemail"];
        $address=$_POST['address'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=isset($_POST['password']) ? $_POST['password'] : '';
        $cpassword=isset($_POST['cpassword']) ? $_POST['cpassword'] : '';
        $id=$_POST['id00'];
        
        if (!empty($password) && $password != $cpassword) {
            $error='2';
        } else {
            $error='3';

            $sqlmain= "select patient.pid from patient inner join webuser on patient.pemail=webuser.email where webuser.email=?;";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("s",$email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["pid"];
            }else{
                $id2=$id;
            }

            if($id2!=$id){
                $error='1';

            }else{
                if (!empty($password)) {
                    // Hash the password for secure storage
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $database->prepare("update patient set pemail=?, pname=?, ppassword=?, pnic=?, ptel=?, paddress=? where pid=?");
                    $stmt->bind_param("ssssssi", $email, $name, $hashed_password, $nic, $tele, $address, $id);
                } else {
                    $stmt = $database->prepare("update patient set pemail=?, pname=?, pnic=?, ptel=?, paddress=? where pid=?");
                    $stmt->bind_param("sssssi", $email, $name, $nic, $tele, $address, $id);
                }
                $stmt->execute();
                $stmt->close();
                
                $stmt = $database->prepare("update webuser set email=? where email=?");
                $stmt->bind_param("ss", $email, $oldemail);
                $stmt->execute();
                $stmt->close();
                
                $error= '4';
                
            }
        }

    }else{
        
        $error='3';
    }

    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    ?>

</body>
</html>

