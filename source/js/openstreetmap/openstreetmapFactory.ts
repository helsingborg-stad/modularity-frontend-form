import Openstreetmap from "./openstreetmap";

class OpenstreetmapFactory {
    public static createOpenstreetmap(openstreetmapContainer: HTMLElement, modularityFrontendForm: ModularityFrontendForm): Openstreetmap|null {
        const id = openstreetmapContainer.id;
        const zoom = openstreetmapContainer.dataset.jsZoom;
        const lat = openstreetmapContainer.dataset.jsLat;
        const lng = openstreetmapContainer.dataset.jsLng;

        if (id && zoom && lat && lng) {
            return new Openstreetmap(modularityFrontendForm, id, parseFloat(lat), parseFloat(lng), parseInt(zoom));
        }

        console.error("OpenstreetmapFactory: Missing required data attributes.");
        return null;
    }
}

export default OpenstreetmapFactory;

