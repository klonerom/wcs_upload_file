<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload de fichier</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script defer src="https://use.fontawesome.com/releases/v5.0.9/js/all.js" integrity="sha384-8iPTk2s/jMVj81dnzb/iFR2sdA7u06vHJyyLlAd4snFpCl/SnyUjRrbdJsw1pGIl" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="row mt-4">
        <h1 class="text-info"><i class="fas fa-upload"></i>&nbsp;Upload d'images</h1>
    </div>
    <div class="row my-4">
        <div class="col-md-6">
            <form action="form.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="exampleInputFile">Files input</label>
                    <input type="file" name="files[]" multiple="multiple" class="form-control-file" id="exampleInputFile" aria-describedby="fileHelp">
                    <small id="fileHelp" class="form-text text-muted">
                        Sélectionnez les images que vous souhaitez uploader.
                    </small>
                </div>
                <button type="submit" value="Upload" class="btn btn-success"><i class="fas fa-plus"></i> Add file</button>
            </form>
        </div>
    </div>

<?php

// chemin vers un dossier sur le serveur qui va recevoir les fichiers uploadés (attention ce dossier doit être accessible en écriture)
$uploadDir = __DIR__ . '/fichier_to/';
//recupération dossier de stockage des images une fois renommée
$folder_destination_name = new SplFileInfo($uploadDir); // /home/rom1 ... /fichier_to
$folder_destination_name = $folder_destination_name->getFilename(); //renvoie fichier_to

if (!empty($_FILES)) {

    //at least 1 file selected
    if ($_FILES['files']['name'][0]) {

        $files = $_FILES['files'];

        $uploaded = [];
        $failed = [];

        $allowed = ['gif', 'png', 'jpg'];

        //Au niveau du tableau name, position représente les index des différentes photos : 0, 1 2. Ces index se retrouve respectivement dans toute les éléments $_FILEs de chaque fichier upload : dans la size, tmp_name ..
        foreach ($files['name'] as $position => $file_value) {

            $file_name = $files['name'][$position];
            $file_tmp = $files['tmp_name'][$position];
            $file_size = $files['size'][$position];
            $file_error = $files['error'][$position];

            // on récupère l'extension, par exemple "jpg"
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //test file type allowed
            if (in_array($file_ext, $allowed)) {

                //test upload error
                if ($file_error === 0) {

                    //test size : 1Mo authorised in this case
                    if ($file_size <= 1048576) {

                        // on concatène le nom de fichier unique avec l'extension récupérée
                        $file_name_new = 'image' . uniqid() . '.' .$file_ext;

                        // on génère un nom de fichier à partir du nom de fichier sur le poste du client (mais vous pouvez générer ce nom autrement si vous le souhaitez)
                        $file_destination = $uploadDir . $file_name_new;

                        // on déplace le fichier temporaire vers le nouvel emplacement sur le serveur. Ca y est, le fichier est uploadé
                        if (move_uploaded_file($file_tmp, $file_destination)) {

                            //stockage des fichiers uploadés
                            $uploaded[$position] = [
                            'file_name_new' => $file_name_new,
                            'file_destination' => $file_destination,
                            'file_size' => $file_size,
                            'file_ext' => $file_ext,
                            ];

                        } else {
                        $failed[$position] = "[$file_name] : failed to upload";
                        }

                    } else {
                    $failed[$position] = "[$file_name] : file size '$file_size' octets > 1Mo max authorized";
                    }

                } else {
                $failed[$position] = "[$file_name] : error with code $file_error";
                }

            } else {
            $failed[$position] = "[$file_name] : file extension '$file_ext' not allowed";
            }

    } //end foreach
?>
    <div class="row">
        <h2 class="text-secondary"><i class="far fa-file-alt"></i>&nbsp;Rapport de chargement des images</h2>
    </div>
<?php

        if (!empty($uploaded)) {
?>
            <div class="row">
                <h3 class="text-success"><i class="fas fa-check"></i>&nbsp;Liste des fichiers chargés</h3>
            </div>
            <div class="row">
<?php
                foreach ($uploaded as $uploaded_value) {
    ?>
                    <div class="col-md-4">
                        <div class="img-thumbnail text-center">
                            <img src="<?= $folder_destination_name . '/' . $uploaded_value['file_name_new'] ?>" alt="<?= $uploaded_value['file_name_new'] ?>" class="rounded img-fluid" width="100%" height="auto">
                            <div class="caption">
                                <p><?= $uploaded_value['file_name_new'] ?></p>
                                <p><a href="form.php?delete=<?= $uploaded_value['file_name_new'] ?>" class="btn btn-danger"><i class="far fa-trash-alt"></i> Delete</a></p>
                            </div>
                        </div>
                    </div>
<?php
                } ?>
            </div>
<?php
        }

        if (!empty($failed)) {
?>
            <div class="row mt-4">
                <h3 class="text-danger"><i class="fas fa-times red"></i>&nbsp;Liste des erreurs après chargement :</h3>
            </div>
            <div class="row">

<?php
            foreach ($failed as $failed_value) {
?>
                <ul>
                    <li><?= $failed_value ?></li>
                </ul>
<?php
            }
       ?> </div>

    <?php }
    ?>
    <div class="row mb-4">
        <a href="form.php" class="btn btn-info mt-4">Retour à la liste</a>
    </div>
    <?php
    }
} else {

    //pour suppression des images uploadees
    if (!empty($_GET['delete'])) {
        if (file_exists($uploadDir . $_GET['delete'])){

            //Suppression image uploadee
            unlink($uploadDir . $_GET['delete']);

            //redirection
            header('Location: form.php');
            die;
        }
    }
        //$folder_files = array_diff(scandir($folder_destination_name, 1), array('..', '.'));

        $folder_files = new FilesystemIterator($folder_destination_name);


        //test si il y a des fichiers dans le dossier
        if (!empty($folder_files->getPathname())) {
        ?>
        <div class="row">
            <h2 class="text-secondary"><i class="fas fa-list-ul"></i>&nbsp;Liste des images présentes dans les médias</h2>
        </div>
        <div class="row">
            <?php
            foreach ($folder_files as $folder_file) {

                $file_name_display = $folder_file->getFilename();
                ?>
                <div class="col-md-4">
                    <div class="img-thumbnail text-center">
                        <img src="<?= $folder_destination_name . '/' . $file_name_display ?>" alt="<?= $file_name_display ?>" class="rounded img-fluid" width="100%" height="auto">
                        <div class="caption">
                            <p><?= $file_name_display ?></p>
                            <p><a href="form.php?delete=<?= $file_name_display ?>" class="btn btn-danger"><i class="far fa-trash-alt"></i> Delete</a></p>
                        </div>
                    </div>
                </div>
            <?php
            }
        }
    }
?>
        </div>
    </div>

</body>
</html>