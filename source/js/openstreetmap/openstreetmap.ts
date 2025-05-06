import { MarkerInterface, CreateMarker, CreateMap, CreateTileLayer, TilesHelper, CreateAttribution, CreateMarkerInterface, MapInterface, CreateSearch, PlaceObject, SearchInterface, EventData, LatLngObject } from '@helsingborg-stad/openstreetmap';

class Openstreetmap implements OpenstreetmapInterface {
    private search!: SearchInterface;
    private marker: MarkerInterface|null = null;
    private map!: MapInterface;
    private createMarker!: CreateMarkerInterface;
    constructor(
        private modularityFrontendFormData: ModularityFrontendFormData,
        private modularityFrontendFormLang: ModularityFrontendFormLang,
        private parent: HTMLElement,
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
    
        this.createMarker = new CreateMarker();

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
                noResultsText: this.modularityFrontendFormLang.noResultsFound ?? 'No items found.',
                placeholder: this.modularityFrontendFormLang.searchPlaceholder ?? 'Search location...',
            })
            .setApiUrl(this.modularityFrontendFormData.placeSearchApiUrl)
            .setSearchParam('q')
            .addTo(this.map)
            .addListItemListener((e) => this.handleListItemClick(e));

        this.map.addListener('click', (e) => this.handleClick(e));
    }

    public hasMarker(): boolean {
        return this.marker !== null;
    }

    private handleClick(e: EventData): void {
        if (!e.latLng) {
            console.error('No latLng found in event data');
            return;
        }

        if (!e.originalEvent) {
            console.error('No originalEvent found in event data');
            return;
        }

        if (e.originalEvent.target && e.originalEvent.target.classList.contains('mod-frontend-form__openstreetmap')) {
            this.addOrMoveMarker(e.latLng);
        }
    }

    private handleListItemClick(item: PlaceObject): void {
        const latLng = {lat: (item.latitude as number) ?? 0, lng: (item.longitude as number) ?? 0};

        this.addOrMoveMarker(latLng);
        this.map.flyTo(latLng, 15);
    }

    private addOrMoveMarker(latLng: LatLngObject): void {
        if (this.marker) {
            this.marker.setPosition(latLng);
        } else {
            this.marker = this.createMarker.create({
                html: this.getIconHtml(),
                position: latLng,
            });

            this.marker.addTo(this.map);
            this.parent.dispatchEvent(new CustomEvent('modularityFrontendFormOpenstreetmapMarkerAdded', {}));
        }
    }

    private getIconHtml(): string {
        return `<span data-material-symbol="location_on" class="mod-frontend-form__openstreetmap-icon material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined material-symbols--filled"></span>`;
    }
}

export default Openstreetmap;