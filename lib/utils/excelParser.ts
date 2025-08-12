import * as XLSX from 'xlsx';

export interface ExcelRow {
  [key: string]: string | number | boolean | null | undefined;
}

export interface ParsedExcelData {
  data: ExcelRow[];
  headers: string[];
  totalRows: number;
  sheetName: string;
}

export interface ExcelParseOptions {
  sheetIndex?: number; // Which sheet to parse (default: 0)
  headerRow?: number; // Which row contains headers (default: 1)
  skipEmptyRows?: boolean; // Skip empty rows (default: true)
  trimValues?: boolean; // Trim whitespace from values (default: true)
}

/**
 * Parse Excel file to JSON
 * @param file - The Excel file to parse
 * @param options - Parsing options
 * @returns Promise with parsed data
 */
export const parseExcelToJson = async (
  file: File,
  options: ExcelParseOptions = {}
): Promise<ParsedExcelData> => {
  const {
    sheetIndex = 0
  } = options;

  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target?.result as ArrayBuffer);
        const workbook = XLSX.read(data, { type: 'array' });

        // Get the specified sheet
        const sheetName = workbook.SheetNames[sheetIndex];
        if (!sheetName) {
          throw new Error(`Sheet at index ${sheetIndex} not found`);
        }

        const worksheet = workbook.Sheets[sheetName];

        // Convert to JSON with options - always skip blank rows
        const jsonData = XLSX.utils.sheet_to_json(worksheet, {
          defval: '', // Default value for empty cells
          blankrows: false // Always skip blank rows
        });

        // Process the data
        let processedData = jsonData as ExcelRow[];

        // Always trim values and filter empty rows
        processedData = processedData
          .map(row => {
            const trimmedRow: ExcelRow = {};
            Object.keys(row).forEach(key => {
              const value = row[key];
              trimmedRow[key] = typeof value === 'string' ? value.trim() : value;
            });
            return trimmedRow;
          })
          .filter(row =>
            Object.values(row).some(value =>
              value !== null && value !== undefined && value !== '' && value !== 0
            )
          );

        // Extract headers
        const headers = processedData.length > 0 ? Object.keys(processedData[0]) : [];

        resolve({
          data: processedData,
          headers,
          totalRows: processedData.length,
          sheetName
        });

      } catch (error) {
        reject(new Error(`Failed to parse Excel file: ${error}`));
      }
    };

    reader.onerror = () => {
      reject(new Error('Failed to read file'));
    };

    reader.readAsArrayBuffer(file);
  });
};

/**
 * Validate if file is a valid Excel file
 * @param file - File to validate
 * @returns boolean
 */
export const isValidExcelFile = (file: File): boolean => {
  const validTypes = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    'application/vnd.ms-excel', // .xls
    'application/vnd.ms-excel.sheet.macroEnabled.12' // .xlsm
  ];

  const validExtensions = ['.xlsx', '.xls', '.xlsm'];
  const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));

  return validTypes.includes(file.type) || validExtensions.includes(fileExtension);
};

/**
 * 
 * @param data - Parsed Excel data
 * @param emailColumn - Column name that contains emails (optional, will auto-detect)
 * @returns Array of email objects
 */
export const extractEmailsFromExcel = (
  data: ExcelRow[],
  emailColumn?: string
): { email: string; name?: string; firstName?: string; lastName?: string; phone?: string | number; code?: string }[] => {
  if (data.length === 0) return [];

  // Auto-detect email column if not provided
  let emailCol = emailColumn;
  if (!emailCol) {
    const headers = Object.keys(data[0]);
    emailCol = headers.find(header =>
      header.toLowerCase().includes('email') ||
      header.toLowerCase().includes('mail') ||
      header.toLowerCase() === 'email' ||
      header.toLowerCase() === 'e-mail' ||
      header.toLowerCase() === 'email address'
    );
  }

  if (!emailCol) {
    console.log('Available columns:', Object.keys(data[0]));
    throw new Error(`No email column found. Available columns: ${Object.keys(data[0]).join(', ')}`);
  }

  return data
    .filter(row => row[emailCol!] && isValidEmail(String(row[emailCol!])))
    .map(row => {
      // Build name from First Name + Last Name
      const firstName = String(row['First Name'] || row['first name'] || row['firstname'] || '');
      const lastName = String(row['Last Name'] || row['last name'] || row['lastname'] || '');
      const fullName = `${firstName} ${lastName}`.trim() || String(row[emailCol!]);
      const phone = row['Phone Number'] || row['phone'] || row['Phone'] || '';
      const code = row['Code'] || row['code'] || row['CODE'] || '';

      return {
        email: String(row[emailCol!]),
        name: fullName,
        firstName: firstName,
        lastName: lastName,
        phone: typeof phone === 'string' ? phone : String(phone),
        code: typeof code === 'string' ? code : String(code)
      };
    });
};

/**
 * Simple email validation
 * @param email - Email to validate
 * @returns boolean
 */
const isValidEmail = (email: string | number | boolean | null | undefined): boolean => {
  if (!email || typeof email !== 'string') return false;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};