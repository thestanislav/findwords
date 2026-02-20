<?php

namespace ExprAs\Core\Response;

use avadim\FastExcelWriter\Excel;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;
use Laminas\Stdlib\ArrayUtils;

class ExcelResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * @var mixed
     */
    protected $payload;

    protected $captions;

    protected $fieldTypes = [];

    public function __construct(
        $data,
        $captions = [],
        int $status = 200,
        array $headers = [],
        array $fieldTypes = []
    ) {
        $this->setPayload($data);
        $this->setCaptions($captions);
        $this->setFieldTypes($fieldTypes);

        $body = $this->createBodyFromData();

        $headers = $this->injectContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $headers);

        parent::__construct($body, $status, $headers);
    }

    /**
     * @return mixed
     */
    public function getCaptions()
    {
        return $this->captions;
    }

    protected function setCaptions(mixed $captions): void
    {
        $this->captions = $captions;
    }

    protected function setFieldTypes(array $fieldTypes): void
    {
        $this->fieldTypes = $fieldTypes;
    }

    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }


    protected function setPayload(mixed $data): void
    {
        $this->payload = $data;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function withPayload(mixed $data): ExcelResponse
    {
        return new self(
            $data,
            $this->getCaptions(),
            $this->getStatusCode(),
            $this->getHeaders()
        );
    }

    public function createBodyFromData()
    {
        // Create Excel workbook
        $excel = Excel::create();
        $sheet = $excel->sheet();

        // Write headers if captions are provided
        if ($captions = $this->getCaptions()) {
            $headerRow = array_values($captions);
            
            // Write header row with bold font
            $sheet->writeRow($headerRow, ['font' => 'bold']);
            
            // Auto-size columns
            $colWidths = [];
            foreach (range('A', chr(64 + count($captions))) as $col) {
                $colWidths[$col] = 'auto';
            }
            $sheet->setColWidths($colWidths);
        }

        // Write data rows
        foreach ($this->getPayload() as $_row) {
            if ($captions = $this->getCaptions()) {
                $rowData = [];
                $rowHeight = 15;
                $cellTypes = [];
                
                foreach ($captions as $_k => $_v) {
                    $value = $this->_extractValue($_k, $_row);
                    $type = $this->fieldTypes[$_k] ?? 'text';
                    
                    // Process value based on type
                    $cellData = $this->processCellValue($value, $type);
                    $rowData[] = $cellData['value'];
                    $cellTypes[] = $cellData['format'] ?? null;
                    
                    // Calculate row height for text wrap
                    $textValue = is_array($cellData['value']) ? ($cellData['value']['text'] ?? '') : $cellData['value'];
                    $rowHeight = max($rowHeight, 14.5 * (substr_count((string)$textValue, "\n") + 1));
                }
                
                // Write row with appropriate formatting
                $this->writeRowWithTypes($sheet, $rowData, $cellTypes, $rowHeight);
            } else {
                $rowData = [];
                $rowHeight = 15;
                
                foreach ($_row as $_v) {
                    $rowData[] = $_v;
                    $rowHeight = max($rowHeight, 14.5 * (substr_count((string) $_v, "\n") + 1));
                }
                
                // Write row with vertical alignment
                $sheet->writeRow($rowData, [
                    'vertical-align' => 'top',
                    'height' => $rowHeight,
                ]);
            }
        }

        // Generate Excel file to memory
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $excel->save($tempFile);
        
        // Read the file into a stream
        $body = new Stream('php://temp', 'wb+');
        $body->write(file_get_contents($tempFile));
        $body->rewind();
        
        // Clean up temp file
        unlink($tempFile);

        return $body;
    }

    protected function _extractValue($key, $data)
    {
        if (($pos = strpos((string) $key, '.'))) {
            return $this->_extractValue(
                substr((string) $key, $pos + 1),
                $data[substr((string) $key, 0, $pos)] ?? []
            );
        }
        if (ArrayUtils::isList($data)) {
            return implode(
                ',',
                array_map(fn ($obj) => $this->_extractValue($key, $obj), $data),
            );
        }
        return $data[$key] ?? null;
    }

    /**
     * Process cell value based on type
     * 
     * @param mixed $value Raw value
     * @param string $type Cell type (text, number, boolean, date, hyperlink)
     * @return array ['value' => mixed, 'format' => string|null]
     */
    protected function processCellValue($value, string $type): array
    {
        switch ($type) {
            case 'hyperlink':
                // Handle hyperlink data
                if (is_array($value) && isset($value['url'])) {
                    // Handle empty/null URL - return empty string instead of hyperlink
                    if (empty($value['url']) && empty($value['text'])) {
                        return [
                            'value' => '',
                            'format' => 'text'
                        ];
                    }
                    return [
                        'value' => [
                            'url' => $value['url'] ?: '',
                            'text' => $value['text'] ?? $value['url'] ?: ''
                        ],
                        'format' => 'hyperlink'
                    ];
                }
                // Handle null/empty value
                if ($value === null || $value === '') {
                    return [
                        'value' => '',
                        'format' => 'text'
                    ];
                }
                // Fallback: treat as URL string
                return [
                    'value' => ['url' => $value, 'text' => $value],
                    'format' => 'hyperlink'
                ];
                
            case 'number':
                return [
                    'value' => is_numeric($value) ? (float)$value : $value,
                    'format' => 'number'
                ];
                
            case 'boolean':
                return [
                    'value' => (bool)$value,
                    'format' => 'boolean'
                ];
                
            case 'date':
                // Date values should already be formatted by hydrator
                return [
                    'value' => $value,
                    'format' => 'date'
                ];
                
            case 'text':
            default:
                return [
                    'value' => $value,
                    'format' => 'text'
                ];
        }
    }

    /**
     * Write row with cell type formatting
     * 
     * @param \avadim\FastExcelWriter\Sheet $sheet
     * @param array $rowData Cell values
     * @param array $cellTypes Cell type formats
     * @param float $rowHeight Row height
     */
    protected function writeRowWithTypes($sheet, array $rowData, array $cellTypes, float $rowHeight): void
    {
        // Write cells individually to apply specific formats
        $row = [];
        foreach ($rowData as $index => $value) {
            $cellType = $cellTypes[$index] ?? 'text';
            
            switch ($cellType) {
                case 'hyperlink':
                    // FastExcelWriter hyperlink format
                    // Check if FastExcelWriter supports hyperlink format
                    if (is_array($value) && isset($value['url']) && !empty($value['url'])) {
                        // Try to use Excel formula format for hyperlink
                        // HYPERLINK(url, text) formula
                        $url = $value['url'];
                        $text = $value['text'] ?? $url;
                        $row[] = '=HYPERLINK("' . $url . '","' . $text . '")';
                    } else {
                        // Empty hyperlink - just write empty string
                        $row[] = '';
                    }
                    break;
                    
                case 'number':
                    $row[] = $value;
                    break;
                    
                case 'boolean':
                    $row[] = $value ? 'TRUE' : 'FALSE';
                    break;
                    
                case 'date':
                    $row[] = $value;
                    break;
                    
                case 'text':
                default:
                    $row[] = $value;
                    break;
            }
        }
        
        // Write the row with formatting
        $sheet->writeRow($row, [
            'text-wrap' => true,
            'vertical-align' => 'top',
            'height' => $rowHeight,
        ]);
    }
}
