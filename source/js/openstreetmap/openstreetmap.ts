import { MarkerInterface, CreateMarker, CreateMap, CreateTileLayer, TilesHelper, CreateAttribution, CreateMarkerInterface, Marker, MapInterface, CreateSearch, PlaceObject, SearchInterface } from '@helsingborg-stad/openstreetmap';

class Openstreetmap {
    private search!: SearchInterface;
    private marker: MarkerInterface|null = null;
    private map!: MapInterface;
    constructor(
        private modularityFrontendForm: ModularityFrontendForm,
        private id: string,
        private lat: number,
        private lng: number,
        private zoom: number
    ) {
    }

    public init(): void {
        this.map = new CreateMap({
            id: this.id,
            center: {
                lat: this.lat,
                lng: this.lng,
            },
            zoom: this.zoom,
        }).create();

        console.log(this.modularityFrontendForm.placeSearchApiUrl)
    
        const createMarker = new CreateMarker();

        const tiles = new TilesHelper().getDefaultTiles('default');
        new CreateAttribution()
            .create()
            .setPrefix(tiles.attribution)
            .addTo(this.map);
        new CreateTileLayer()
            .create()
            .setUrl(tiles.url)
            .addTo(this.map);
        this.search = new CreateSearch()
            .create({
                noResultsText: this.modularityFrontendForm.lang.noResultsText ?? 'No items found.',
                placeholder: this.modularityFrontendForm.lang.searchPlaceholder ?? 'Search location...',
            })
            .setApiUrl(this.modularityFrontendForm.placeSearchApiUrl)
            .setSearchParam('q')
            .addTo(this.map)
            .addListItemListener((e) => this.handleClick(e, createMarker));
    }

    private handleClick(item: PlaceObject, createMarker: CreateMarkerInterface): void {
        const latLng = {lat: (item.latitude as number) ?? 0, lng: (item.longitude as number) ?? 0};

        if (this.marker) {
            this.marker.setPosition(latLng);
        } else {
            this.marker = createMarker.create({
                html: this.getIconHtml(),
                position: latLng,
            });

            this.marker.addTo(this.map);
        }

        this.map.flyTo(latLng, 15);
    }

    private getIconHtml(): string {
        return `<span data-material-symbol="location_on" class="mod-frontend-form__openstreetmap-icon material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined material-symbols--filled"></span>`;
    }
}

export default Openstreetmap;