<section id="user-list" class="mt-8">
    <!-- Search Box -->
    <div class="mb-4">
        <input type="text" id="search-input" class="block w-full p-2 border rounded" placeholder="Search by name">
    </div>

    <!-- User List Table -->
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody id="user-list-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($users as $user)
            <tr class="{{ $user->suspended ? 'bg-red-300' : '' }}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            @if($user->hasRole('admin'))
                            <svg class="inline-block w-4 h-4 text-blue-500 tooltip" data-tippy-content="This is an admin user." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                <path d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z" />
                            </svg>
                            @endif
                            {{ $user->name }}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->created_at->format('F j, Y') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    @if($user->suspended)
                    <button class="text-indigo-600 hover:text-indigo-900" onclick="toggleSuspend({{ $user->id }})">
                        <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 144c0-44.2 35.8-80 80-80c31.9 0 59.4 18.6 72.3 45.7c7.6 16 26.7 22.8 42.6 15.2s22.8-26.7 15.2-42.6C331 33.7 281.5 0 224 0C144.5 0 80 64.5 80 144l0 48-16 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64l-240 0 0-48z" />
                        </svg>
                    </button>
                    @else
                    <button class="text-indigo-600 hover:text-indigo-900" onclick="toggleSuspend({{ $user->id }})">
                        <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                            <path d="M144 144l0 48 160 0 0-48c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192l0-48C80 64.5 144.5 0 224 0s144 64.5 144 144l0 48 16 0c35.3 0 64 28.7 64 64l0 192c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 256c0-35.3 28.7-64 64-64l16 0z" />
                        </svg>
                    </button>
                    @endif
                    @if($user->force_password_reset)
                    <svg class="w-6 h-6 inline-block tooltip" data-tippy-content="This user has a pending forced password reset." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0l57.4-43c23.9-59.8 79.7-103.3 146.3-109.8l13.9-10.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176 0 384c0 35.3 28.7 64 64 64l296.2 0C335.1 417.6 320 378.5 320 336c0-5.6 .3-11.1 .8-16.6l-26.4 19.8zM640 336a144 144 0 1 0 -288 0 144 144 0 1 0 288 0zm-76.7-43.3c6.2 6.2 6.2 16.4 0 22.6l-72 72c-6.2 6.2-16.4 6.2-22.6 0l-40-40c-6.2-6.2-6.2-16.4 0-22.6s16.4-6.2 22.6 0L480 353.4l60.7-60.7c6.2-6.2 16.4-6.2 22.6 0z" />
                    </svg>
                    @else
                    <button class="text-yellow-600 hover:text-yellow-900" onclick="resetPassword({{ $user->id }})">
                        <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17l0 80c0 13.3 10.7 24 24 24l80 0c13.3 0 24-10.7 24-24l0-40 40 0c13.3 0 24-10.7 24-24l0-40 40 0c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z" />
                        </svg>
                    </button>
                    @endif
                    <button class="text-red-600 hover:text-red-900" onclick="deleteUser({{ $user->id }})">
                        <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                            <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L353.3 251.6C407.9 237 448 187.2 448 128C448 57.3 390.7 0 320 0C250.2 0 193.5 55.8 192 125.2L38.8 5.1zM264.3 304.3C170.5 309.4 96 387.2 96 482.3c0 16.4 13.3 29.7 29.7 29.7l388.6 0c3.9 0 7.6-.7 11-2.1l-261-205.6z" />
                        </svg>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $users->appends(['query' => $query])->links() }}
    </div>

    <!-- JavaScript for Search Box -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const userTableBody = document.getElementById('user-list-body');

            searchInput.addEventListener('input', function() {
                const filter = searchInput.value;

                axios.get('/admin/users/search', {
                        params: {
                            query: filter
                        }
                    })
                    .then(response => {
                        const users = response.data;
                        userTableBody.innerHTML = '';

                        users.forEach(user => {
                            const userRow = document.createElement('tr');
                            userRow.className = `user-row ${user.suspended ? 'bg-red-300' : ''}`;

                            userRow.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    ${user.is_admin ? `<svg class="inline-block w-4 h-4 text-blue-500 tooltip" data-tippy-content="This is an admin user." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z" />
                                    </svg>` : ''}
                                    ${user.name}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">${user.email}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">${new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${user.suspended 
                                ? `<button class="text-indigo-600 hover:text-indigo-900" onclick="toggleSuspend(${user.id})">
                                    <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                        <path d="M144 144c0-44.2 35.8-80 80-80c31.9 0 59.4 18.6 72.3 45.7c7.6 16 26.7 22.8 42.6 15.2s22.8-26.7 15.2-42.6C331 33.7 281.5 0 224 0C144.5 0 80 64.5 80 144l0 48-16 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192c0-35.3-28.7-64-64-64l-240 0 0-48z" />
                                    </svg>
                                </button>` 
                                : `<button class="text-indigo-600 hover:text-indigo-900" onclick="toggleSuspend(${user.id})">
                                    <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                        <path d="M144 144l0 48 160 0 0-48c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192l0-48C80 64.5 144.5 0 224 0s144 64.5 144 144l0 48 16 0c35.3 0 64 28.7 64 64l0 192c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 256c0-35.3 28.7-64 64-64l16 0z" />
                                    </svg>
                                </button>`}
                            ${user.force_password_reset 
                                ? `<svg class="w-6 h-6 inline-block tooltip" data-tippy-content="This user has a pending forced password reset." xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                    <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0l57.4-43c23.9-59.8 79.7-103.3 146.3-109.8l13.9-10.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176 0 384c0 35.3 28.7 64 64 64l296.2 0C335.1 417.6 320 378.5 320 336c0-5.6 .3-11.1 .8-16.6l-26.4 19.8zM640 336a144 144 0 1 0 -288 0 144 144 0 1 0 288 0zm-76.7-43.3c6.2 6.2 6.2 16.4 0 22.6l-72 72c-6.2 6.2-16.4 6.2-22.6 0l-40-40c-6.2-6.2-6.2-16.4 0-22.6s16.4-6.2 22.6 0L480 353.4l60.7-60.7c6.2-6.2 16.4-6.2 22.6 0z"/>
                                </svg>`
                                : `<button class="text-yellow-600 hover:text-yellow-900" onclick="resetPassword(${user.id})">
                                    <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17l0 80c0 13.3 10.7 24 24 24l80 0c13.3 0 24-10.7 24-24l0-40 40 0c13.3 0 24-10.7 24-24l0-40 40 0c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z" />
                                    </svg>
                                </button>`}
                            <button class="text-red-600 hover:text-red-900" onclick="deleteUser(${user.id})">
                                <svg class="w-6 h-6 inline-block transition duration-300 ease-in-out hover:opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                                    <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L353.3 251.6C407.9 237 448 187.2 448 128C448 57.3 390.7 0 320 0C250.2 0 193.5 55.8 192 125.2L38.8 5.1zM264.3 304.3C170.5 309.4 96 387.2 96 482.3c0 16.4 13.3 29.7 29.7 29.7l388.6 0c3.9 0 7.6-.7 11-2.1l-261-205.6z"/>
                                </svg>
                            </button>
                        </td>
                    `;

                            userTableBody.appendChild(userRow);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                    });
            });
        });

        function toggleSuspend(userId) {
            axios.post('/admin/users/suspend', {
                    user_id: userId
                })
                .then(response => {
                    if (response.data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.data.success,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Reload the page to reflect changes
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to suspend user.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error suspending user:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while suspending the user.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }



        function resetPassword(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to submit a password reset?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reset it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/admin/users/reset-password', {
                            user_id: userId
                        })
                        .then(response => {
                            if (response.data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.data.success,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload(); // Reload the page to reflect changes
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.data.error,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error resetting password:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while resetting the password.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }


        function deleteUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/admin/users/${userId}`)
                        .then(response => {
                            if (response.data.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.data.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload(); // Reload the page to reflect changes
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to delete user.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting user:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the user.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }
    </script>
</section>