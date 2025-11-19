import { LatLngObject, PlaceObject } from "@helsingborg-stad/openstreetmap";

class CreateFallbackPlaceObject {
    public create(latLng: LatLngObject): PlaceObject {
        return {
            '@type': 'Place',
            latitude: latLng.lat,
            longitude: latLng.lng,
            address: {
                '@type': 'PostalAddress',
                streetAddress: `${latLng.lat}, ${latLng.lng}`,
                name: `${latLng.lat}, ${latLng.lng}`,
                addressCountry: '',
                addressLocality: '',
                addressRegion: '',
                postalCode: '',
            }
        };
    }
}

export default CreateFallbackPlaceObject;