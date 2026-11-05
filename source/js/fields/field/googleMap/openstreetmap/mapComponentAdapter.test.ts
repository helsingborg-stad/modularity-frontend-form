import prepareGoogleMapsForFormControls from './mapComponentAdapter';

describe('prepareGoogleMapsForFormControls', () => {
	beforeEach(() => {
		document.body.innerHTML = '';
	});

	it('disables the component initializer for form maps while preserving their map configuration', () => {
		document.body.innerHTML = `
			<div class="mod-frontend-form__openstreetmap">
				<div id="location_map" data-js-map="location_map" data-js-map-lat="59.3" data-js-map-lng="18.0" data-js-map-zoom="14"></div>
			</div>
			<div class="other-map">
				<div id="public_map" data-js-map="public_map"></div>
			</div>
		`;

		prepareGoogleMapsForFormControls();

		const formMap = document.querySelector('#location_map') as HTMLElement;
		const otherMap = document.querySelector('#public_map') as HTMLElement;

		expect(formMap.hasAttribute('data-js-map')).toBe(false);
		expect(formMap.dataset.jsFormMap).toBe('location_map');
		expect(formMap.dataset.jsMapLat).toBe('59.3');
		expect(formMap.dataset.jsMapLng).toBe('18.0');
		expect(formMap.dataset.jsMapZoom).toBe('14');
		expect(otherMap.getAttribute('data-js-map')).toBe('public_map');
	});

	it('reports maps that do not provide an ID', () => {
		const error = jest.spyOn(console, 'error').mockImplementation(() => {});
		document.body.innerHTML = `
			<div class="mod-frontend-form__openstreetmap">
				<div data-js-map=""></div>
			</div>
		`;

		prepareGoogleMapsForFormControls();

		expect(error).toHaveBeenCalledWith('Map component is missing its map ID.');
		error.mockRestore();
	});
});
