<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    
    /**
     * Creates a modal to be displayed on the page with the given parameters
     * @param string $id The ID of the modal, to allow JS to access it
     * @param string $title The title of the modal to be displayed
     * @param string $content The content of the modal (HTML code to be displayed)
     * @param string $content_type The type of content to be displayed in the modal
     * @param string $width The width of the modal (Must include units, e.g '600px')
     *
     * @return string The HTML code for the modal
     */
    function createModal(string $id, string $title, string $content, string $content_type='single', string $width="600px"): string
    {
        return "
            <div class='modal-container' id='$id'>
            <div class='modal' style='width: $width;'>
                
                <h1>$title</h1>
                <div class='modal-content-$content_type'>$content</div>
                
                <div class='modal-buttons'>
                    <button class='modal-apply' id='$id-apply'>Apply</button>
                    <button class='modal-cancel' id='$id-cancel'>Cancel</button>
                </div>
                
                <div class='modal-error' id='$id-error'>
                    <p></p>
                </div>
                
            </div>
        </div>
        ";
    }