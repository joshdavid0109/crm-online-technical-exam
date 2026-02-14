import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Customer } from '../models/customer.model';


@Injectable({
  providedIn: 'root'
})
export class CustomerService {

  private apiUrl = 'http://localhost:8000/api/customers';

  constructor(private http: HttpClient) {}

  getCustomers(search?: string) {
    if (search) {
      return this.http.get<{ data: Customer[] }>(`${this.apiUrl}?search=${search}`);
    }
    return this.http.get<{ data: Customer[] }>(this.apiUrl);
  }

  getCustomer(id: number) {
    return this.http.get<{ data: Customer }>(`${this.apiUrl}/${id}`);
  }

  createCustomer(data: Partial<Customer>) {
    return this.http.post<Customer>(this.apiUrl, data);
  }

  updateCustomer(id: number, data: Partial<Customer>) {
    return this.http.put<Customer>(`${this.apiUrl}/${id}`, data);
  }

  deleteCustomer(id: number) {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }
}

