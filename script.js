document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ JavaScript loaded successfully!");

    /** ======================= HOME PAGE ======================= **/
    const getStartedButton = document.querySelector(".start");
    if (getStartedButton) {
        getStartedButton.addEventListener("click", () => {
            window.location.href = "register.html";
        });
    } else {
        console.warn("⚠️ 'Get Started' button not found.");
    }

    /** ======================= REGISTRATION ======================= **/
    const registerForm = document.getElementById("register-form");
    if (registerForm) {
        registerForm.addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("register.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("✅ " + data.message);
                    setTimeout(() => {
                        window.location.href = "login.html";
                    }, 1000);
                } else {
                    alert("❌ " + data.message);
                }
            })
            .catch(error => console.error("❌ Registration error:", error));
        });
    } else {
        console.warn("⚠️ Registration form not found.");
    }

    /** ======================= LOGIN ======================= **/
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("login.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("✅ " + data.message);
                    setTimeout(() => {
                        window.location.href = "todo.php";
                    }, 1000);
                } else {
                    alert("❌ " + data.message);
                }
            })
            .catch(error => console.error("❌ Login error:", error));
        });
    } else {
        console.warn("⚠️ Login form not found.");
    }

    /** ======================= TO-DO APP ======================= **/
    const taskTitle = document.getElementById("task-title");
    if (!taskTitle) {
        console.log("⚠️ Not on to-do page. Skipping task scripts.");
        return;
    }

    const taskCategory = document.getElementById("task-category");
    const taskDue = document.getElementById("task-due");
    const addTaskButton = document.getElementById("add-task");
    const taskList = document.getElementById("task-list");
    const filterAll = document.getElementById("filter-all");
    const filterCompleted = document.getElementById("filter-completed");
    const filterPending = document.getElementById("filter-pending");

    if (!addTaskButton || !taskList) {
        console.warn("⚠️ Task elements not found. Exiting script.");
        return;
    }

    /** === UČITAVANJE ZADATAKA === **/
    function loadTasks() {
        fetch("get_tasks.php")
        .then(response => response.json())
        .then(tasks => {
            taskList.innerHTML = "";

            tasks.forEach(task => {
                const taskItem = document.createElement("li");
                taskItem.classList.add("task-item");
                taskItem.setAttribute("data-id", task.id);
                taskItem.setAttribute("data-status", task.status);

                if (task.status === "completed") {
                    taskItem.classList.add("completed");
                }

                taskItem.innerHTML = `
                    <span class="task-text">${task.description} - <em>${task.category}</em> (Due: ${task.due_date})</span>
                    <div class="task-buttons">
                        <button class="complete-task">✔</button>
                        <button class="delete-task">✖</button>
                    </div>
                `;

                taskList.appendChild(taskItem);
            });
        })
        .catch(error => console.error("Error loading tasks:", error));
    }

    /** === DODAVANJE NOVOG ZADATKA === **/
    if (addTaskButton) {
        addTaskButton.addEventListener("click", () => {
            if (taskTitle.value.trim() === "") {
                alert("Please enter a task title.");
                return;
            }

            let formData = new FormData();
            formData.append("description", taskTitle.value);
            formData.append("category", taskCategory.value);
            formData.append("due_date", taskDue.value);

            fetch("add_task.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTasks();
                }
            })
            .catch(error => console.error("Error adding task:", error));

            taskTitle.value = "";
            taskDue.value = "";
        });
    }

    /** === OZNAČAVANJE ZADATAKA KAO ZAVRŠENIH === **/
    taskList.addEventListener("click", (e) => {
        if (e.target.classList.contains("complete-task")) {
            const taskItem = e.target.closest(".task-item");
            const taskId = taskItem.getAttribute("data-id");

            fetch("update_task.php", {
                method: "POST",
                body: new URLSearchParams({ task_id: taskId })
            })
            .then(response => response.text())
            .then(() => {
                taskItem.classList.toggle("completed"); 
                taskItem.setAttribute("data-status", taskItem.classList.contains("completed") ? "completed" : "pending");
            })
            .catch(error => console.error("Error updating task:", error));
        }
    });

    /** === BRISANJE ZADATAKA === **/
    taskList.addEventListener("click", (e) => {
        if (e.target.classList.contains("delete-task")) {
            const taskItem = e.target.closest(".task-item");
            const taskId = taskItem.getAttribute("data-id");

            fetch("delete_task.php", {
                method: "POST",
                body: new URLSearchParams({ task_id: taskId })
            })
            .then(response => response.text())
            .then(() => {
                taskItem.remove();
            })
            .catch(error => console.error("Error deleting task:", error));
        }
    });

    /** === FILTRIRANJE ZADATAKA === **/
    if (filterAll) {
        filterAll.addEventListener("click", () => {
            document.querySelectorAll(".task-item").forEach(task => task.style.display = "flex");
        });
    }

    if (filterCompleted) {
        filterCompleted.addEventListener("click", () => {
            document.querySelectorAll(".task-item").forEach(task => {
                task.style.display = task.getAttribute("data-status") === "completed" ? "flex" : "none";
            });
        });
    }

    if (filterPending) {
        filterPending.addEventListener("click", () => {
            document.querySelectorAll(".task-item").forEach(task => {
                task.style.display = task.getAttribute("data-status") === "pending" ? "flex" : "none";
            });
        });
    }

    /** === UČITAVANJE ZADATAKA PRI POKRETANJU STRANICE === **/
    loadTasks();
});
