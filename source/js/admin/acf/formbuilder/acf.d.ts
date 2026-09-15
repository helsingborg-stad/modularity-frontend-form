type AcfField = {
    $el: JQuery<HTMLElement>;
};

declare const acf: {
    addAction(action: string, callback: (...args: any[]) => void): void;
};
