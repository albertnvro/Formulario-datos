<?php
/*
Plugin Name: Plugin para integrar la muestra y edición de los campos de usuario en el perfil de usuario de WordPress.
Plugin URI: http://hovered.es/
Description: Un plugin completo para permitir a los usuarios actualizar su correo electrónico, contraseña, DNI, nombre y apellidos mediante un shortcode, con medidas de seguridad adicionales.
Version: 1.0
Author: Albert Navarro
Author URI: https://www.linkedin.com/in/albert-n-579261256/
*/

function mi_formulario_de_usuario_shortcode() {
    if (!is_user_logged_in()) {
        return 'Por favor, inicia sesión para editar tu perfil.';
    }

    $output = '';
    $user_id = get_current_user_id();
    $current_user = get_userdata($user_id);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nonce_formulario_usuario']) && wp_verify_nonce($_POST['nonce_formulario_usuario'], 'editar_usuario')) {
        // Manejar actualización de email y DNI independientemente
        if (!empty($_POST['email'])) {
            if (!is_email($_POST['email'])) {
                $output .= 'Por favor, introduce una dirección de correo electrónico válida.<br>';
            } elseif (email_exists($_POST['email']) && $_POST['email'] != $current_user->user_email) {
                $output .= 'El correo electrónico ya está en uso por otro usuario.<br>';
            } else {
                wp_update_user(array('ID' => $user_id, 'user_email' => sanitize_email($_POST['email'])));
                $output .= 'Correo electrónico actualizado correctamente.<br>';
            }
        }

        // Actualizar DNI
        function  validarDniEsp($dni){
        if (isset($_POST['dni'])) {
            update_user_meta($user_id, 'dni', sanitize_text_field($_POST['dni']));
            $output .= 'DNI actualizado correctamente.<br>';
        }
        }   

        // Actualizar nombres y apellidos
        if (isset($_POST['first_name'])) {
            update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
            $output .= 'Nombre actualizado correctamente.<br>';
        }
        if (isset($_POST['last_name'])) {
            update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
            $output .= 'Apellido actualizado correctamente.<br>';
        }

        // Actualización de contraseña si ambos campos están llenos y coinciden
        if (!empty($_POST['pass1']) && !empty($_POST['pass2'])) {
            if ($_POST['pass1'] == $_POST['pass2']) {
                wp_set_password($_POST['pass1'], $user_id);
                $output .= 'Contraseña actualizada correctamente.<br>';
            } else {
                $output .= 'Las contraseñas no coinciden.<br>';
            }
        }
    }

    // Formulario HTML
    $output .= '<form method="post">';
    $output .= wp_nonce_field('editar_usuario', 'nonce_formulario_usuario', true, false);
    $output .= 'Correo electrónico: <input type="email" name="email" value="' . esc_attr($current_user->user_email) . '"><br>';
    $output .= 'DNI: <input type="text" name="dni" value="' . esc_attr(get_user_meta($user_id, 'dni', true)) . '"><br>';
    $output .= 'Nombre: <input type="text" name="first_name" value="' . esc_attr($current_user->first_name) . '"><br>';
    $output .= 'Apellido: <input type="text" name="last_name" value="' . esc_attr($current_user->last_name) . '"><br>';
    $output .= 'Nueva contraseña: <input type="password" name="pass1"><br>';
    $output .= 'Repetir nueva contraseña: <input type="password" name="pass2"><br>';
    $output .= '<input type="submit" value="Actualizar">';
    $output .= '</form>';

    return $output;
}

function registrar_mi_shortcode() {
    add_shortcode('formulario_usuario', 'mi_formulario_de_usuario_shortcode');
}

add_action('show_user_profile', 'mi_formulario_de_usuario_shortcode' )
add_action('edit_profile_user_update', 'mi_formulario_de_usuario_shortcode')
add_action('init', 'registrar_mi_shortcode');
