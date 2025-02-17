
        function showToast() {
            const params = getQueryParams();
            if (params.message) {
                const responseDiv = document.getElementById("responseDiv");
                const toastMessage = document.getElementById("toastMessage");
                toastMessage.textContent = params.message;
                responseDiv.classList.add(params.class);
                responseDiv.style.display = "block";
            }
        }

        function hideToast() {
            const responseDiv = document.getElementById("responseDiv");
            responseDiv.style.display = "none";
        }

        window.onload = showToast;