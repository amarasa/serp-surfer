<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Newsletter Builder') }}
        </h2>
        <button id="send-email" class="mt-4 p-2 bg-blue-500 text-white rounded">Send Email</button>

    </x-slot>
    <style>
        .editor-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        #blocks {
            width: 230px;
            /* Fixed width for the sidebar */
            overflow-y: auto;
            background-color: #f4f4f4;
            border-right: 1px solid #ddd;
            padding: 10px;
        }

        #gjs {
            flex-grow: 1;
            /* Editor takes up all remaining space */
            height: 100vh;
            /* Full viewport height */
        }

        .gjs-editor {
            height: 100% !important;
            /* Ensure the editor height is 100% */
        }

        .gjs-frame-wrapper {
            height: calc(100% - 40px) !important;
            /* Adjust height for toolbar */
        }
    </style>
    <div class="editor-container">
        <div id="blocks"></div> <!-- Sidebar for blocks -->
        <div id="gjs"></div> <!-- Main GrapesJS container -->
    </div>

</x-app-layout>