<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Laravel/Retrofit Songs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-100 p-8">
    <nav class="bg-cyan-700 text-white p-4 rounded flex justify-between mb-6 shadow">
        <div class="font-semibold text-lg">Panel CRUD</div>
        <div class="space-x-6">
            <a href="/songs" class="underline font-bold">Canciones</a>
            <a href="/albums" class="hover:underline">Albumes</a>
        </div>
    </nav>

    <h1 class="text-2xl font-semibold mb-6">Canciones</h1>

    <div class="flex gap-3">
        <button onclick="openCreateDialog()" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded">
            Crear canciones
        </button>

        <button onclick="loadSongs()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
            <i class="fa-solid fa-rotate-right mr-2"></i>Recargar
        </button>
    </div>

    <table class="min-w-full mt-6 bg-white shadow rounded-lg overflow-hidden">
        <thead class="bg-cyan-600 text-white">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Cancion</th>
                <th class="p-3 text-left">Duracion</th>
                <th class="p-3 text-left">Lyrics</th>
                <th class="p-3 text-left">Album</th>
                <th class="p-3 text-left">Acciones</th>
            </tr>
        </thead>

        <tbody id="songsTable"></tbody>
    </table>

    <div id="dialog" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-96 shadow-xl animate-fadeIn">
            <h2 id="dialogTitle" class="text-xl font-semibold mb-4"></h2>

            <input type="hidden" id="songId">

            <label class="block mb-2">Titulo:</label>
            <input type="text" id="songTitulo" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Duracion:</label>
            <input type="text" id="songDuration" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Letra:</label>
            <input type="text" id="songLyric" class="w-full border p-2 rounded mb-4">

            <label class="block mb-2">Album:</label>
            <select id="albumSelect" class="w-full border p-2 rounded mb-4"></select>

            <div class="flex justify-end gap-3">
                <button onclick="closeDialog()" class="px-3 py-2 rounded border">Cancelar</button>
                <button id="dialogSubmit" class="px-3 py-2 rounded bg-cyan-600 text-white"></button>
            </div>
        </div>
    </div>

<script>
const API = "http://127.0.0.1:8000/api/v1";

document.addEventListener("DOMContentLoaded", () => {
    loadAlbums();
    loadSongs();
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

function loadSongs() {
    fetch(`${API}/songs`, { headers: { "Accept": "application/json" } })
        .then(r => {
            if (!r.ok) throw new Error("Error HTTP");
            return r.json();
        })
        .then(data => {
            const table = document.getElementById("songsTable");
            table.innerHTML = "";

            data.forEach(s => {
                const title = s.name ?? s.title ?? "";
                const lyrics = s.lyrics ?? "";
                const albumName = s.album?.album_name ?? s.album_id ?? "";

                const safeTitle = escapeJsString(title);
                const safeDuration = escapeJsString(s.duration);
                const safeLyrics = escapeJsString(lyrics);

                table.innerHTML += `
                    <tr class="border-b">
                        <td class="p-3">${s.id}</td>
                        <td class="p-3">${title}</td>
                        <td class="p-3">${s.duration}</td>
                        <td class="p-3">${lyrics}</td>
                        <td class="p-3">${albumName}</td>
                        <td class="p-3 space-x-3">
                            <button onclick="openEditDialog(${s.id}, '${safeTitle}', '${safeDuration}', '${safeLyrics}', ${s.album_id})"
                                class="text-blue-600 text-lg">
                                <i class="fa-solid fa-pencil"></i>
                            </button>

                            <button onclick="deleteSong(${s.id})"
                                class="text-red-600 text-lg">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(() => toast("Error cargando Canciones", "red"));
}

function loadAlbums() {
    fetch(`${API}/albums`, { headers: { "Accept": "application/json" } })
        .then(r => {
            if (!r.ok) throw new Error("Error HTTP");
            return r.json();
        })
        .then(data => {
            const select = document.getElementById("albumSelect");
            select.innerHTML = "";
            data.forEach(a => {
                select.innerHTML += `<option value="${a.id}">${a.album_name}</option>`;
            });
        })
        .catch(() => toast("Error cargando Albumes", "red"));
}

function openCreateDialog() {
    document.getElementById("dialogTitle").textContent = "Crear Cancion";
    document.getElementById("dialogSubmit").textContent = "Guardar";

    document.getElementById("songId").value = "";
    document.getElementById("songTitulo").value = "";
    document.getElementById("songDuration").value = "";
    document.getElementById("songLyric").value = "";

    const select = document.getElementById("albumSelect");
    if (select.options.length > 0) {
        select.selectedIndex = 0;
    }

    document.getElementById("dialogSubmit").onclick = createSong;
    document.getElementById("dialog").classList.remove("hidden");
}

function openEditDialog(id, title, duration, lyrics, albumId) {
    document.getElementById("dialogTitle").textContent = "Editar Cancion";
    document.getElementById("dialogSubmit").textContent = "Actualizar";

    document.getElementById("songId").value = id;
    document.getElementById("songTitulo").value = title;
    document.getElementById("songDuration").value = duration;
    document.getElementById("songLyric").value = lyrics;
    document.getElementById("albumSelect").value = albumId;

    document.getElementById("dialogSubmit").onclick = updateSong;
    document.getElementById("dialog").classList.remove("hidden");
}

function closeDialog() {
    document.getElementById("dialog").classList.add("hidden");
}

function getSongPayload() {
    return {
        name: document.getElementById("songTitulo").value.trim(),
        duration: document.getElementById("songDuration").value.trim(),
        lyrics: document.getElementById("songLyric").value.trim(),
        album_id: Number(document.getElementById("albumSelect").value)
    };
}

function createSong() {
    const payload = getSongPayload();

    fetch(`${API}/songs`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
            const firstError = data.errors
                ? Object.values(data.errors).flat()[0]
                : (data.message || "Error al crear");
            throw new Error(firstError);
        }
        return data;
    })
    .then(() => {
        closeDialog();
        toast("Cancion creada");
        loadSongs();
    })
    .catch(err => toast(err.message || "Error al crear", "red"));
}

function updateSong() {
    const id = document.getElementById("songId").value;
    const payload = getSongPayload();

    fetch(`${API}/songs/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
            const firstError = data.errors
                ? Object.values(data.errors).flat()[0]
                : (data.message || "Error al actualizar");
            throw new Error(firstError);
        }
        return data;
    })
    .then(() => {
        closeDialog();
        toast("Cancion actualizada", "#22c55e");
        loadSongs();
    })
    .catch(err => toast(err.message || "Error al actualizar", "red"));
}

function deleteSong(id) {
    if (!confirm("¿Eliminar esta cancion?")) return;

    fetch(`${API}/songs/${id}`, {
        method: "DELETE",
        headers: { "Accept": "application/json" }
    })
    .then(r => {
        if (!r.ok) throw new Error("Error al eliminar");
        return r.json();
    })
    .then(() => {
        toast("Cancion eliminada", "orange");
        loadSongs();
    })
    .catch(() => toast("Error al eliminar", "red"));
}
</script>
</body>
</html>