<div class="p-3">
    <h5 class="text-primary mb-3">
        <i class="fas fa-map-marked-alt"></i> {{ $zoneData['name'] }}
    </h5>

    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Departamento:</strong> {{ $zoneData['department'] }}</li>
        <li class="list-group-item"><strong>Provincia:</strong> {{ $zoneData['province'] }}</li>
        <li class="list-group-item"><strong>Distrito:</strong> {{ $zoneData['district'] }}</li>
        <li class="list-group-item"><strong>Estado:</strong>
            <span class="badge badge-{{ $zoneData['status'] ? 'success' : 'danger' }}">
                {{ $zoneData['status'] ? 'Activo' : 'Inactivo' }}
            </span>
        </li>
        <li class="list-group-item"><strong>Descripción:</strong> {{ $zoneData['description'] ?? 'Sin descripción' }}</li>
    </ul>

    <div id="zoneMap" style="height: 400px; border-radius: 8px; border: 1px solid #ddd;"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const zone = @json($zoneData);

    // Inicializar mapa
    const map = L.map('zoneMap').setView([zone.coordinates[0].lat, zone.coordinates[0].lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    
    mapGeneral = L.map('mapView').setView([-6.7716, -79.8441], 13);


    // Crear polígono
    const polygonCoords = zone.coordinates.map(c => [c.lat, c.lng]);
    const polygon = L.polygon(polygonCoords, {
        color: '#007bff',
        fillColor: '#007bff',
        fillOpacity: 0.4,
        weight: 3
    }).addTo(map);

    map.fitBounds(polygon.getBounds());

    // Popup con info básica
    polygon.bindPopup(`
        <strong>${zone.name}</strong><br>
        ${zone.district}, ${zone.province}<br>
        ${zone.description || 'Sin descripción'}
    `).openPopup();
});
</script>

<style>
    #mapView {
    height: 550px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

</style>
