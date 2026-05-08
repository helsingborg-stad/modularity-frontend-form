import { MapInterface } from '@helsingborg-stad/openstreetmap';

type MarkerChangedCallback = (event: PlaceObject | null) => void;
interface OpenstreetmapInterface {
	init(): void;
	hasPlaceData(): boolean;
	addMarkerMovedListener(listener: (event: PlaceObject | null) => void): void;
	addOrMoveMarker(latLng: LatLngObject, placeObject?: PlaceObject | null): void;
	getMarker(): MarkerInterface | null;
	isFetchingPlace(): boolean;
	getMap(): MapInterface;
}
