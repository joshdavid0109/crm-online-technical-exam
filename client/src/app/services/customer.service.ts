import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CustomerService {

  private apiUrl = 'http://localhost:8000/api/customers';

  constructor(private http: HttpClient) {}

  getCustomers(search: string = ''): Observable<any[]> {
    if (search) {
      return this.http.get<any[]>(`${this.apiUrl}?search=${search}`);
    }
    return this.http.get<any[]>(this.apiUrl);
  }

  createCustomer(data: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, data);
  }

  updateCustomer(id: number, data: any): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}`, data);
  }

  deleteCustomer(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/${id}`);
  }
}
