function prepareGoogleMapsForFormControls(): void {
	document.querySelectorAll<HTMLElement>('.mod-frontend-form__openstreetmap [data-js-map]').forEach((mapContainer) => {
		const mapId = mapContainer.dataset.jsMap;

		if (!mapId) {
			console.error('Map component is missing its map ID.');
			return;
		}

		// The form's map logic initializes this component's container itself.
		mapContainer.dataset.jsFormMap = mapId;
		mapContainer.removeAttribute('data-js-map');
	});
}

export default prepareGoogleMapsForFormControls;
