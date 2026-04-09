import CheckboxLayout from "./layouts/checkboxLayout";
import DateLayout from "./layouts/dateLayout";
import EmailLayout from "./layouts/emailLayout";
import FileLayout from "./layouts/fileLayout";
import GalleryLayout from "./layouts/galleryLayout";
import GoogleMapLayout from "./layouts/googleMapLayout";
import ImageLayout from "./layouts/imageLayout";
import MessageLayout from "./layouts/messageLayout";
import NumberLayout from "./layouts/numberLayout";
import RadioLayout from "./layouts/radioLayout";
import SelectLayout from "./layouts/selectLayout";
import TextLayout from "./layouts/textLayout";
import TextareaLayout from "./layouts/textareaLayout";
import TimeLayout from "./layouts/timeLayout";
import TrueFalseLayout from "./layouts/trueFalseLayout";
import UrlLayout from "./layouts/urlLayout";
import WysiwygLayout from "./layouts/wysiwygLayout";
import BasicLayoutUI from "./layouts/templates/basicUi";
import SelectableValuesLayoutUI from "./layouts/templates/selectableValuesLayoutUi";

export type BasicLayoutConstructor = new (layoutData: LayoutData, layoutUI: BasicLayoutUI) => BasicLayoutInterface;
export type SelectableLayoutConstructor = new (layoutData: LayoutData, layoutUI: SelectableValuesLayoutUI) => SelectableValuesLayoutInterface;

export type BasicLayoutDefinition = {
    type: string;
    kind: 'basic';
    LayoutClass: BasicLayoutConstructor;
};

export type SelectableLayoutDefinition = {
    type: string;
    kind: 'selectable';
    LayoutClass: SelectableLayoutConstructor;
};

export type LayoutDefinition = BasicLayoutDefinition | SelectableLayoutDefinition;

export const layoutDefinitions: LayoutDefinition[] = [
    { type: 'checkbox', kind: 'selectable', LayoutClass: CheckboxLayout },
    { type: 'date', kind: 'basic', LayoutClass: DateLayout },
    { type: 'email', kind: 'basic', LayoutClass: EmailLayout },
    { type: 'file', kind: 'basic', LayoutClass: FileLayout },
    { type: 'gallery', kind: 'basic', LayoutClass: GalleryLayout },
    { type: 'googleMap', kind: 'basic', LayoutClass: GoogleMapLayout },
    { type: 'image', kind: 'basic', LayoutClass: ImageLayout },
    { type: 'message', kind: 'basic', LayoutClass: MessageLayout },
    { type: 'number', kind: 'basic', LayoutClass: NumberLayout },
    { type: 'radio', kind: 'selectable', LayoutClass: RadioLayout },
    { type: 'select', kind: 'selectable', LayoutClass: SelectLayout },
    { type: 'text', kind: 'basic', LayoutClass: TextLayout },
    { type: 'textarea', kind: 'basic', LayoutClass: TextareaLayout },
    { type: 'time', kind: 'basic', LayoutClass: TimeLayout },
    { type: 'trueFalse', kind: 'basic', LayoutClass: TrueFalseLayout },
    { type: 'url', kind: 'basic', LayoutClass: UrlLayout },
    { type: 'wysiwyg', kind: 'basic', LayoutClass: WysiwygLayout }
];
