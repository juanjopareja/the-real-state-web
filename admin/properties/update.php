<?php
    require '../../includes/functions.php';
    $auth = isAuthenticated();

    if(!$auth) {
        header('location: ../index.php');
    }

    // URL validation per ID
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if(!$id) {
        header('Location: ../../admin');
    }

    // DataBase
    require '../../includes/config/database.php';
    $db = connectDB();

    // Get property data
    $query = "SELECT * FROM properties WHERE id = $id";
    $result = mysqli_query($db, $query);
    $property = mysqli_fetch_assoc($result);

    // Sellers query
    $query_seller = "SELECT * FROM sellers";
    $result_seller = mysqli_query($db, $query_seller);

    // Error Messages
    $errors = [];

    $title = $property['title'];
    $price = $property['price'];
    $description = $property['description'];
    $bedrooms = $property['bedrooms'];
    $wc = $property['wc'];
    $parking = $property['parking'];
    $seller_id = $property['sellers_id'];
    $imageProperty = $property['image'];
    
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = mysqli_real_escape_string($db, $_POST['title']);
        $price = mysqli_real_escape_string($db, $_POST['price']);
        $description = mysqli_real_escape_string($db, $_POST['description']);
        $bedrooms = mysqli_real_escape_string($db, $_POST['bedrooms']);
        $wc = mysqli_real_escape_string($db, $_POST['wc']);
        $parking = mysqli_real_escape_string($db, $_POST['parking']);
        $seller_id = mysqli_real_escape_string($db, $_POST['seller']);
        $created = date('Y/m/d');

        // Asign files to a variable
        $image = $_FILES['image'];


        if(!$title) {
            $errors[] = "Debes añadir un título";
        }

        if(!$price) {
            $errors[] = "Debes añadir un precio";
        }

        if(strlen($description) < 50){
            $errors[] = "La descripción es obligatoria y debe tener al menos 50 caracteres";
        }

        if(!$bedrooms) {
            $errors[] = "El número de habitaciones es obligatorio";
        }

        if(!$wc) {
            $errors[] = "El número de baños es obligatorio";
        }

        if(!$parking) {
            $errors[] = "El número de plazas de garage es obligatorio";
        }

        if(!$seller_id) {
            $errors[] = "Elige un vendedor";
        }

        // Validate image size (1Mb max)
        $size = 1000 * 1000;

        if($image['size'] > $size) {
            $errors[] = "La imagen supera el tamaño máximo de archivo (100kb)";
        }

        if(empty($errors)) {
            /** Files Upload */ 

            // Create Folder
            $imageFolder = '../../images';
            if(!is_dir($imageFolder)) {
                mkdir($imageFolder);
            }

            $imageName = '';

            if($image['name']) {
                // Delete previous image
                unlink($imageFolder . "/" . $property['image']);

                // Generate Unique Name
                $imageName = md5( uniqid( rand(), ) ) . ".jpg";
    
                // Upload Image
                move_uploaded_file($image['tmp_name'], $imageFolder . "/" . $imageName);
            } else {
                $imageName = $property['image'];
            }

            // DB Update
            $query = "UPDATE properties SET title = '$title', price = '$price', image = '$imageName', description = '$description', 
            bedrooms = $bedrooms, wc = $wc, parking = $parking, sellers_id = $seller_id WHERE id = $id";
    
            $result = mysqli_query($db, $query);
    
            if($result) {
                // Redirect User
                header('Location: ../index.php?result=2');
            }
        }

    }   

    includeTemplate('header');
?>

    <main class="container section">
        <h1>Actualizar Propiedad</h1>

        <a href="../index.php" class="button green-button">Volver</a>

        <?php foreach($errors as $error) { ?>

        <div class="alert error">
            <?php echo $error; ?>
        </div>

        <?php } ?>

        <form class="form" method="POST" enctype="multipart/form-data">
            <fieldset>
                <legend>Información General</legend>

                <label for="title">Título:</label>
                <input type="text" id="title" name="title" placeholder="Título Propiedad" value="<?php echo $title; ?>">

                <label for="price">Precio:</label>
                <input type="number" id="price" name="price" placeholder="Precio Propiedad" value="<?php echo $price; ?>">

                <label for="image">Imagen:</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png">
                <img src="../../images/<?php echo $imageProperty; ?>" alt="imagen propiedad" class="small-image">

                <label for="description">Descripción:</label>
                <textarea id="description" name="description"><?php echo $description; ?></textarea>
            </fieldset>

            <fieldset>
                <legend>Información Propiedad</legend>

                <label for="bedrooms">Habitaciones:</label>
                <input type="number" id="bedrooms" name="bedrooms" placeholder="Ej: 3" min="1" max="9" value="<?php echo $bedrooms; ?>">

                <label for="wc">Baños:</label>
                <input type="number" id="wc" name="wc" placeholder="Ej: 2" min="1" max="9" value="<?php echo $wc; ?>">

                <label for="parking">Parking:</label>
                <input type="number" id="parking" name="parking" placeholder="Ej: 1" min="1" max="9" value="<?php echo $parking; ?>">
            </fieldset>

            <fieldset>
                <legend>Vendedor</legend>

                <select name="seller">
                    <option value="">-- Selecciona --</option>
                    <?php while($seller = mysqli_fetch_assoc($result_seller)) {?>
                        <option <?php echo $seller_id === $seller['id'] ? 'selected' : ''; ?> value="<?php echo $seller['id']; ?>"> <?php echo $seller['name'] . " " . $seller['lastname']; ?></option>
                    <?php } ?>
                    
                </select>
            </fieldset>

            <input type="submit" value="Actualizar Propiedad" class="button green-button">
        </form>
    </main>

<?php
    includeTemplate('footer');
?>