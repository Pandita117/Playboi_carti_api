<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Laravel/Retrofit Albums</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-100 p-8">

    <nav class="bg-cyan-700 text-white p-4 rounded flex justify-between mb-6 shadow">
        <div class="font-semibold text-lg">Panel CRUD</div>
        <div class="space-x-6">
            <a href="/songs" class="hover:underline">Songs</a>
            <a href="/albums" class="underline font-bold">Albumes</a>
        </div>
    </nav>

    <h1 class="text-2xl font-semibold mb-6">Albumes</h1>

    <div class="flex gap-3">
        <button onclick="openCreateDialog()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded">
            Crear Album
        </button>

        <button onclick="loadAlbums()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            <i class="fa-solid fa-rotate-right mr-2"></i>Recargar
        </button>
    </div>

    <table class="min-w-full mt-6 bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-cyan-600 text-white">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Nombre album</th>
                <th class="p-3 text-left">Lanzamiento</th>
                <th class="p-3 text-left">Duracion</th>
                <th class="p-3 text-left">Descripcion</th>
                <th class="p-3 text-left">Acciones</th>
            </tr>
        </thead>

        <tbody id="albumsTable"></tbody>
    </table>

    <div id="dialog" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-96 shadow-xl animate-fadeIn">
            <h2 id="dialogTitle" class="text-xl font-semibold mb-4"></h2>

            <input type="hidden" id="albumId">

            <label class="block mb-2">Nombre:</label>
            <input type="text" id="albumName" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Lanzamiento (YYYY-MM-DD):</label>
            <input type="date" id="albumRelease" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Duracion (segundos):</label>
            <input type="number" id="albumLong" min="1" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Descripcion:</label>
            <input type="text" id="albumDescription" class="w-full border p-2 rounded mb-4">

            <div class="flex justify-end gap-3">
                <button onclick="closeDialog()" class="px-3 py-2 rounded border">Cancelar</button>
                <button id="dialogSubmit" class="px-3 py-2 rounded bg-cyan-600 text-white"></button>
            </div>
        </div>
    </div>

<script>
const API = "http://localhost:8000/api/v1";

document.addEventListener("DOMContentLoaded", () => {
    loadAlbums();
});

function toast(msg, color = "#0ea5e9") {
    Toastify({
        text: msg,
        duration: 2000,
        gravity: "top",
        position: "right",
        style: { background: color }
    }).showToast();
}

function escapeJsString(str) {
    return String(str ?? "")
        .replace(/\\/g, "\\\\")
        .replace(/'/g, "\\'")
        .replace(/"/g, "&quot;");
}

function loadAlbums() {
    fetch(`${API}/albums`)
        .then(r => {
            if (!r.ok) throw new Error("Error HTTP");
            return r.json();
        })
        .then(data => {
            const table = document.getElementById("albumsTable");
            table.innerHTML = "";

            data.forEach(a => {
                const safeName = escapeJsString(a.album_name);
                const safeRelease = escapeJsString(a.release_date);
                const safeLong = Number(a.long) || 0;
                const safeDesc = escapeJsString(a.description);

                table.innerHTML += `
                    <tr class="border-b">
                        <td class="p-3">${a.id}</td>
                        <td class="p-3">${a.album_name}</td>
                        <td class="p-3">${a.release_date}</td>
                        <td class="p-3">${a.long}</td>
                        <td class="p-3">${a.description}</td>
                        <td class="p-3 space-x-3">
                            <button onclick="openEditDialog(${a.id}, '${safeName}', '${safeRelease}', ${safeLong}, '${safeDesc}')"
                                class="text-blue-600 text-lg">
                                <i class="fa-solid fa-pencil"></i>
                            </button>

                            <button onclick="deleteAlbum(${a.id})"
                                class="text-red-600 text-lg">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(() => toast("Error cargando albums", "red"));
}

function openCreateDialog() {
    document.getElementById("dialogTitle").textContent = "Crear album";
    document.getElementById("dialogSubmit").textContent = "Guardar";

    document.getElementById("albumId").value = "";
    document.getElementById("albumName").value = "";
    document.getElementById("albumRelease").value = "";
    document.getElementById("albumLong").value = "";
    document.getElementById("albumDescription").value = "";

    document.getElementById("dialogSubmit").onclick = createAlbum;
    document.getElementById("dialog").classList.remove("hidden");
}

function openEditDialog(id, albumName, releaseDate, long, description) {
    document.getElementById("dialogTitle").textContent = "Editar album";
    document.getElementById("dialogSubmit").textContent = "Actualizar";

    document.getElementById("albumId").value = id;
    document.getElementById("albumName").value = albumName;
    document.getElementById("albumRelease").value = releaseDate;
    document.getElementById("albumLong").value = long;
    document.getElementById("albumDescription").value = description;

    document.getElementById("dialogSubmit").onclick = updateAlbum;
    document.getElementById("dialog").classList.remove("hidden");
}

function closeDialog() {
    document.getElementById("dialog").classList.add("hidden");
}

function getFormPayload() {
    return {
        album_name: document.getElementById("albumName").value.trim(),
        release_date: document.getElementById("albumRelease").value.trim(),
        long: Number(document.getElementById("albumLong").value),
        description: document.getElementById("albumDescription").value.trim()
    };
}

function createAlbum() {
    const payload = getFormPayload();

    fetch(`${API}/albums`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if (!r.ok) {
            const err = await r.json().catch(() => ({}));
            throw new Error(err.message || "Error al crear");
        }
        return r.json();
    })
    .then(() => {
        closeDialog();
        toast("Album creado");
        loadAlbums();
    })
    .catch(err => toast(err.message || "Error al crear", "red"));
}

function updateAlbum() {
    const id = document.getElementById("albumId").value;
    const payload = getFormPayload();

    fetch(`${API}/albums/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        if (!r.ok) {
            const err = await r.json().catch(() => ({}));
            throw new Error(err.message || "Error al actualizar");
        }
        return r.json();
    })
    .then(() => {
        closeDialog();
        toast("Album actualizado", "#22c55e");
        loadAlbums();
    })
    .catch(err => toast(err.message || "Error al actualizar", "red"));
}

function deleteAlbum(id) {
    if (!confirm("¿Eliminar este album?")) return;

    fetch(`${API}/albums/${id}`, {
        method: "DELETE",
        headers: { "Accept": "application/json" }
    })
    .then(r => {
        if (!r.ok) throw new Error("Error al eliminar");
        return r.json();
    })
    .then(() => {
        toast("Album eliminado", "orange");
        loadAlbums();
    })
    .catch(() => toast("Error al eliminar", "red"));
}
</script>

</body>
</html>