import { PlaceObject } from "@helsingborg-stad/openstreetmap";

class FetchPlaceFromLatLng {
	constructor(private modularityFrontendFormData: ModularityFrontendFormData) {}

	public async fetch(lat: number, lng: number): Promise<PlaceObject> {
		const url = `${this.modularityFrontendFormData.placeSearchApiUrl}?lat=${lat}&lng=${lng}&reverse=true`;
		const response = await fetch(url);

		if (!response.ok) {
			throw new Error("Network response was not ok");
		}

		const data = await response.json();
		return data;
	}
}

export default FetchPlaceFromLatLng;
