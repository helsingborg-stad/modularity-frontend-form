type MarkerChangedCallback = (event: PlaceObject | null) => void;
interface OpenstreetmapInterface {
	init(): void;
	hasPlaceData(): boolean;
	addMarkerMovedListener(listener: (event: PlaceObject | null) => void): void;
}
