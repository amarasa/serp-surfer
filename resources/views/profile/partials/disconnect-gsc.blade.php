<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Google Search Console Connected') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Your account is connected to Google Search Console.') }}
        </p>
        <form id="disconnect-form" method="POST" action="{{ route('auth.google.disconnect') }}">
            @csrf
            <button type="button" id="disconnect-button" class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 dark:hover:bg-red-500 focus:bg-red-500 dark:focus:bg-red-500 active:bg-red-700 dark:active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Disconnect
            </button>
        </form>
    </header>
</section>
<script>
    document.getElementById('disconnect-button').addEventListener('click', function(event) {
        event.preventDefault();

        // Create a SweetAlert2 popup with a custom HTML element for the toggle switch
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            html: `
                <style>
                    .toggle-switch {
                        position: relative;
                        display: inline-block;
                        width: 40px;
                        height: 20px;
                    }
                    .toggle-switch input {
                        opacity: 0;
                        width: 0;
                        height: 0;
                    }
                    .slider {
                        position: absolute;
                        cursor: pointer;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #ccc;
                        transition: 0.4s;
                        border-radius: 20px;
                    }
                    .slider:before {
                        position: absolute;
                        content: "";
                        height: 14px;
                        width: 14px;
                        left: 3px;
                        bottom: 3px;
                        background-color: white;
                        transition: 0.4s;
                        border-radius: 50%;
                    }
                    input:checked + .slider {
                        background-color: #2196F3;
                    }
                    input:checked + .slider:before {
                        transform: translateX(20px);
                    }
                </style>
                <p>Do you also want to delete all your sitemaps from our database?</p>
                <label class="toggle-switch">
                    <input type="checkbox" id="delete-sitemaps-toggle">
                    <span class="slider"></span>
                </label>
            `,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, disconnect it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Check the state of the toggle switch
                var deleteSitemaps = document.getElementById('delete-sitemaps-toggle').checked;

                // Show success message based on the toggle switch state
                Swal.fire({
                    title: "Disconnected!",
                    text: deleteSitemaps ? "Your Google Search Console has been disconnected and sitemaps deleted." : "Your Google Search Console has been successfully disconnected.",
                    icon: "success"
                }).then(() => {
                    // Set a hidden input in the form to indicate the state of the toggle
                    var form = document.getElementById('disconnect-form');
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_sitemaps';
                    input.value = deleteSitemaps ? '1' : '0';
                    form.appendChild(input);

                    // Submit the form
                    form.submit();
                });
            }
        })
    });
</script>