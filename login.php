<?php
    require 'includes/functions.php';
    includeTemplate('header');
?>

    <main class="container section center-content">
        <h1>Iniciar Sesión</h1>

        <form action="" class="form">
        <fieldset>
                <legend>Email y Password</legend>
                
                <label for="email">E-mail:</label>
                <input type="email" placeholder="Tu Email" id="email">

                <label for="password">Password:</label>
                <input type="password" placeholder="Tu password" id="password">
            </fieldset>

            <input type="submit" value="Iniciar Sesión" class="button green-button">
        </form>
    </main>

<?php
    includeTemplate('footer');
?>