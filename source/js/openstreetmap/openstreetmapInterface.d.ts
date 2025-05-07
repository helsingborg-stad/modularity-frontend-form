interface OpenstreetmapInterface {
    init(): void;
    hasMarker(): boolean;
    addMarkerMovedListener(listener: (event: PlaceObject) => void): void;
}