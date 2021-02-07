interface SizesData {
    numUp: number,
    breakpointValue: number,
    breakpointUnits: string,
    rowPaddingValue: number,
    rowPaddingUnits: string,
    cellPaddingValue: number,
    cellPaddingUnits: string,
}

type SizesDataList = SizesData[];

interface AspectRatioObj {
    ratioX: number,
    ratioY: number,
    useAspectRatio: boolean,
}
