
    <?php

    include("../connection.php");

    if($_POST){
        
        $result= $database->query("select * from webuser");
        $name=$_POST['name'];
        $oldemail=$_POST["oldemail"];
        $nic=$_POST['nic'];
        $spec=$_POST['spec'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00'];
        
        if ($password==$cpassword){
            $error='3';
            $result= $database->query("select doctor.docid from doctor inner join webuser on doctor.docemail=webuser.email where webuser.email='$email';");
            
            if($result->num_rows==1){
                $id2=$result->fetch_assoc()["docid"];
            }else{
                $id2=$id;
            }
            
            echo $id2."jdfjdfdh";
            if($id2!=$id){
                $error='1';

            }else{
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $database->prepare("update doctor set docemail=?,docname=?,docpassword=?,docnic=?,doctel=?,specialties=? where docid=?");
                    $stmt->bind_param("sssssii", $email, $name, $hashed_password, $nic, $tele, $spec, $id);
                } else {
                    $stmt = $database->prepare("update doctor set docemail=?,docname=?,docnic=?,doctel=?,specialties=? where docid=?");
                    $stmt->bind_param("ssssii", $email, $name, $nic, $tele, $spec, $id);
                }
                $stmt->execute();
                $stmt->close();

                $stmt = $database->prepare("update webuser set email=? where email=?");
                $stmt->bind_param("ss", $email, $oldemail);
                $stmt->execute();
                $stmt->close();
                
                $error= '4';
                
            }
            
        }else{
            $error='2';
        }

    }else{
        
        $error='3';
    }

    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    ?>

</body>
</html>

