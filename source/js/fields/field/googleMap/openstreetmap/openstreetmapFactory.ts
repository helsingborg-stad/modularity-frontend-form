import Openstreetmap from "./openstreetmap";
import FetchPlaceFromLatLng from "./fetchPlaceFromLatLng";
import CreateFallbackPlaceObject from "./createFallbackPlaceObject";

class OpenstreetmapFactory {
	public static createOpenstreetmap(
		openstreetmapContainer: HTMLElement,
		modularityFrontendFormData: ModularityFrontendFormData,
		modularityFrontendFormLang: ModularityFrontendFormLang,
	): Openstreetmap | null {
		if (!modularityFrontendFormData.placeSearchApiUrl) {
			console.error("OpenstreetmapFactory: Missing place search API URL");
			return null;
		}

		const map = openstreetmapContainer.querySelector<HTMLElement>("[data-js-form-map]");

		if (!map) {
			console.error("OpenstreetmapFactory: Missing map element.");
			return null;
		}

		const id = map.dataset.jsFormMap;
		const zoom = map.dataset.jsMapZoom ?? "14";
		const lat = map.dataset.jsMapLat;
		const lng = map.dataset.jsMapLng;

		if (id && zoom && lat && lng) {
			return new Openstreetmap(
				new FetchPlaceFromLatLng(modularityFrontendFormData),
				new CreateFallbackPlaceObject(),
				modularityFrontendFormData,
				modularityFrontendFormLang,
				openstreetmapContainer,
				id,
				parseFloat(lat),
				parseFloat(lng),
				parseInt(zoom),
			);
		}

		console.error("OpenstreetmapFactory: Missing required data attributes.");
		return null;
	}
}

export default OpenstreetmapFactory;
