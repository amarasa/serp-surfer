function timeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    let interval = Math.floor(seconds / 31536000);

    if (interval >= 1)
        return interval + " year" + (interval > 1 ? "s" : "") + " ago";
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1)
        return interval + " month" + (interval > 1 ? "s" : "") + " ago";
    interval = Math.floor(seconds / 86400);
    if (interval >= 1)
        return interval + " day" + (interval > 1 ? "s" : "") + " ago";
    interval = Math.floor(seconds / 3600);
    if (interval >= 1)
        return interval + " hour" + (interval > 1 ? "s" : "") + " ago";
    interval = Math.floor(seconds / 60);
    if (interval >= 1)
        return interval + " minute" + (interval > 1 ? "s" : "") + " ago";
    return Math.floor(seconds) + " second" + (seconds > 1 ? "s" : "") + " ago";
}

document.addEventListener("DOMContentLoaded", function () {
    const dropdownButton = document.getElementById("dropdown-button");
    const dropdownMenu = document.getElementById("dropdown-menu");
    const selectedItem = document.getElementById("selected-item");
    const items = dropdownMenu.querySelectorAll("li");

    // Toggle dropdown visibility when the button is clicked
    dropdownButton.addEventListener("click", function () {
        dropdownMenu.classList.toggle("hidden");
    });

    items.forEach((item) => {
        item.addEventListener("click", function () {
            const selectedText = item.querySelector(".block").innerText;
            selectedItem.innerText = selectedText;

            items.forEach((i) =>
                i.querySelector("span + span").classList.add("hidden")
            );
            item.querySelector("span + span").classList.remove("hidden");

            dropdownMenu.classList.add("hidden");

            if (selectedText !== "Select Domain") {
                fetchUrlsForDomain(selectedText);
            }
        });
    });

    // Hide dropdown when clicking outside of it
    document.addEventListener("click", function (event) {
        if (
            !dropdownButton.contains(event.target) &&
            !dropdownMenu.contains(event.target)
        ) {
            dropdownMenu.classList.add("hidden");
        }
    });
});
