import { Component, OnInit } from '@angular/core';
import { CustomerService } from '../../services/customer.service';
import { Subject, debounceTime } from 'rxjs';

@Component({
  selector: 'app-customer',
  templateUrl: './customer.component.html',
})
export class CustomerComponent implements OnInit {

  customers: any[] = [];
  searchTerm: string = '';

  loading: boolean = false;
  showModal: boolean = false;

  searchSubject: Subject<string> = new Subject<string>();

  currentPage: number = 1;
  itemsPerPage: number = 5;

  formData: any = {
    first_name: '',
    last_name: '',
    email: '',
    contact_number: '',
    contact_prefix: '+63'
  };

  countries = [
    {
      code: '+63',
      label: '🇵🇭 +63',
      length: 10,
      format: [3, 3, 4] // 912 345 6789
    },
    {
      code: '+61',
      label: '🇦🇺 +61',
      length: 9,
      format: [3, 3, 3] // 412 345 678
    },
    {
      code: '+1',
      label: '🇺🇸 +1',
      length: 10,
      format: [3, 3, 4] // 123 456 7890
    },
    {
      code: '+44',
      label: '🇬🇧 +44',
      length: 10,
      format: [4, 3, 3] // 7700 900 123
    }
  ];

  isFieldValid(field: any): boolean {
    return field.valid && field.touched;
  } 

  editingId: number | null = null;

  showDeleteModal: boolean = false;
  customerToDeleteId: number | null = null;
  emailExistsError: boolean = false;
  phoneLengthError: boolean = false;

  showMaxDigitsTooltip: boolean = false;


  constructor(private customerService: CustomerService) {}

  ngOnInit(): void {
    this.loadCustomers();

    this.searchSubject
      .pipe(debounceTime(400))
      .subscribe(() => {
        this.currentPage = 1;
        this.loadCustomers();
      });
  }

  loadCustomers(): void {
    this.loading = true;

    this.customerService.getCustomers(this.searchTerm)
      .subscribe({
        next: (data) => {
          this.customers = data;
          this.loading = false;
        },
        error: () => {
          this.loading = false;
        }
      });
  }

  onSearch(): void {
    this.searchSubject.next(this.searchTerm);
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.resetForm();
    this.showModal = false;
  }

 submitForm(form: any): void {

    if (form.invalid) return;

    this.loading = true;
    this.emailExistsError = false;

    const rawNumber = this.formData.contact_number.replace(/\s/g, '');

    const expectedLength = this.selectedCountry?.length;

    if (expectedLength && rawNumber.length !== expectedLength) {
      this.phoneLengthError = true;
      this.loading = false;
      return;
    }

    this.phoneLengthError = false;


    const payload = {
      ...this.formData,
      contact_number: `${this.formData.contact_prefix}${this.formData.contact_number}`
    };

    const request = this.editingId
      ? this.customerService.updateCustomer(this.editingId, payload)
      : this.customerService.createCustomer(payload);


    request.subscribe({
      next: () => {
        this.closeModal();
        this.loadCustomers();
      },
      error: (err) => {
        this.loading = false;

        if (err.status === 422 && err.error?.errors?.email) {
          this.emailExistsError = true;
        }
      }
    });
  }

  editCustomer(customer: any): void {

    const fullNumber: string = customer.contact_number || '';

    // Find matching country
    const matchedCountry = this.countries.find(c =>
      fullNumber.startsWith(c.code)
    );

    if (matchedCountry) {
      this.formData.contact_prefix = matchedCountry.code;

      const numberWithoutPrefix = fullNumber.replace(matchedCountry.code, '');

      this.formData.contact_number = numberWithoutPrefix;
      this.formatPhoneInput();
    } else {
      // fallback
      this.formData.contact_prefix = '+63';
      this.formData.contact_number = fullNumber;
    }

    this.formData.first_name = customer.first_name;
    this.formData.last_name = customer.last_name;
    this.formData.email = customer.email;

    this.editingId = customer.id;
    this.showModal = true;
  }


  deleteCustomer(id: number): void {
    this.customerToDeleteId = id;
    this.showDeleteModal = true;
  }

  resetForm(): void {
    this.formData = {
      first_name: '',
      last_name: '',
      email: '',
      contact_number: '',
      contact_prefix: '+63'
    };
    this.editingId = null;
  }

  confirmDelete(): void {
    if (!this.customerToDeleteId) return;

    this.loading = true;

    this.customerService.deleteCustomer(this.customerToDeleteId)
      .subscribe({
        next: () => {
          this.showDeleteModal = false;
          this.customerToDeleteId = null;
          this.loadCustomers();
        },
        error: () => {
          this.loading = false;
        }
      });
  }

  cancelDelete(): void {
    this.showDeleteModal = false;
    this.customerToDeleteId = null;
  }

  formatPhoneInput(event?: any): void {

    const country = this.selectedCountry;
    if (!country) return;

    let digits = this.formData.contact_number
      ? this.formData.contact_number.replace(/\D/g, '')
      : '';

    // HARD LIMIT: prevent extra digits
    if (digits.length > country.length) {
      digits = digits.slice(0, country.length);
    }

    // Format per country
    let formatted = '';
    let index = 0;

    for (let group of country.format) {
      if (digits.length > index) {
        formatted += digits.slice(index, index + group) + ' ';
        index += group;
      }
    }

    this.formData.contact_number = formatted.trim();

    // Live validation
    this.phoneLengthError =
      digits.length > 0 && digits.length !== country.length;
  }

  onPrefixChange(): void {
    this.formatPhoneInput();
  }

  preventOverflow(event: KeyboardEvent): void {

    const country = this.selectedCountry;
    if (!country) return;

    const currentDigits = this.formData.contact_number
      ? this.formData.contact_number.replace(/\D/g, '')
      : '';

    if (currentDigits.length >= country.length) {
      event.preventDefault();
    }
  }

  triggerMaxDigitsTooltip(): void {
    this.showMaxDigitsTooltip = true;

    setTimeout(() => {
      this.showMaxDigitsTooltip = false;
    }, 1200); // auto hide after 1.2s
  }


  blockInvalidKeys(event: KeyboardEvent): void {

    const allowedKeys = [
      'Backspace',
      'Delete',
      'ArrowLeft',
      'ArrowRight',
      'Tab'
    ];

    // Allow control keys
    if (allowedKeys.includes(event.key)) return;

    // Block non-numeric keys
    if (!/^\d$/.test(event.key)) {
      event.preventDefault();
      return;
    }

    // Enforce max length
    const country = this.selectedCountry;
    if (!country) return;

    const digits = this.formData.contact_number
      ? this.formData.contact_number.replace(/\D/g, '')
      : '';

    if (digits.length >= country.length) {
      event.preventDefault();
      this.triggerMaxDigitsTooltip();
    }

  }

  handlePaste(event: ClipboardEvent): void {

    event.preventDefault();

    const pasteData = event.clipboardData?.getData('text') || '';

    // Remove everything except digits
    const digitsOnly = pasteData.replace(/\D/g, '');

    const country = this.selectedCountry;
    if (!country) return;

    const limited = digitsOnly.slice(0, country.length);

    if (digitsOnly.length > country.length) {
      this.triggerMaxDigitsTooltip();
    }

    this.formData.contact_number = limited;
    this.formatPhoneInput();
  }


  get paginatedCustomers() {
    const start = (this.currentPage - 1) * this.itemsPerPage;
    return this.customers.slice(start, start + this.itemsPerPage);
  }

  get totalPages() {
    return Math.ceil(this.customers.length / this.itemsPerPage) || 1;
  }
  
  get selectedCountry() {
    return this.countries.find(c => c.code === this.formData.contact_prefix);
  }
}
