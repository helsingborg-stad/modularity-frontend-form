import { PlaceObject } from '@helsingborg-stad/openstreetmap';

class GoogleMapValueLoader implements GoogleMapValueLoaderInterface {
	private parent!: GoogleMapInterface;

	constructor(private openstreetmapInstance: OpenstreetmapInterface) {}

	public init(parent: GoogleMapInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		const placeObject = this.tryToConvertValueToPlaceObject(value);
		console.log(placeObject);
		// TODO: implement value loading
	}

	private tryToConvertValueToPlaceObject(value: any): PlaceObject | null {
		if (typeof value !== 'object') {
			return null;
		}

		if (this.isNotAValidPlaceObject(value)) {
			// return null;
		}

		const placeObject: PlaceObject = {
			'@type': 'Place',
		};

		return value;
	}

	private isNotAValidPlaceObject(value: any): boolean {
		if (typeof value !== 'object' || !value.lat || !value.lng || !value.country || !value.postal_code) {
			return true;
		}
		return false;
	}
}

export default GoogleMapValueLoader;
