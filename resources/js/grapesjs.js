import grapesjs from "grapesjs";
import "grapesjs/dist/css/grapes.min.css"; // Import GrapesJS core CSS
import grapesjsPresetNewsletter from "grapesjs-preset-newsletter"; // Import the plugin

document.addEventListener("DOMContentLoaded", () => {
    const editor = grapesjs.init({
        container: "#gjs",
        height: "100%", // Set editor height to fill the container
        width: "auto", // Auto width to fit container
        storageManager: { autoload: 0 },
        plugins: [grapesjsPresetNewsletter], // Register the plugin
        pluginsOpts: {
            "gjs-preset-newsletter": {
                blocks: [
                    "column1",
                    "column2",
                    "column3",
                    "text",
                    "link",
                    "image",
                    "quote",
                    "divider",
                    "grid-items",
                    "list-items",
                    "button",
                    "form",
                    "form-input",
                    "form-textarea",
                    "form-button",
                    "form-select",
                    "form-checkbox",
                    "form-radio",
                ],
            },
        },
        blockManager: {
            appendTo: "#blocks", // Attach the block manager to the sidebar
        },
        panels: { defaults: [] }, // Ensure no default panels conflict with the sidebar
    });

    // Add styles for the container to fill the window
    editor.getContainer().style.height = "100vh";

    // Add an event listener to export the content
    document.getElementById("send-email").addEventListener("click", () => {
        const html = editor.getHtml(); // Get the HTML content
        const css = editor.getCss(); // Get the CSS content
        sendEmail(html, css); // Function to handle sending the email
    });
});

// Function to handle sending the email (this function will be implemented on the server side)
// Function to send email content
function sendEmail(html, css) {
    fetch("/send-email", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"), // CSRF token
        },
        body: JSON.stringify({ html, css }),
    })
        .then((response) => response.json())
        .then((data) => {
            alert(data.success);
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while sending the email.");
        });
}
