<?php
/**
 * Customer Service Chat Widget Template Part
 * 
 * Logic to display Facebook Messenger, LINE OA, or Custom Scripts.
 * Controlled by Theme Options > Customer Service.
 */

defined( 'ABSPATH' ) || exit;

$enable_chat = carbon_get_theme_option('chat_enable');
if (!$enable_chat) {
    return;
}

$chat_script = carbon_get_theme_option('chat_script');
if ($chat_script) {
    echo $chat_script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw embed code entered by administrators (manage_options).
}
