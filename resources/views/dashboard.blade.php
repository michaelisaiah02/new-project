@extends('layouts.app')
@section('title', 'INFORMATION')
@section('content')
    <div class="container-fluid mt-2">
        <div class="card mb-2">
            <div class="card-body p-2">
                <span class="badge bg-none text-dark shadow fs-5 border-2 border-secondary-subtle mb-1">New Project</span>
                <div class="table-responsive overflow-y-auto" style="max-height: 135px;">
                    <table class="table table-sm table-bordered table-hover m-0">
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col">NO</th>
                                <th scope="col">Costumer</th>
                                <th scope="col">Model</th>
                                <th scope="col">Message</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="">
                                <td scope="row">R1C1</td>
                                <td>R1C2</td>
                                <td>R1C3</td>
                                <td>R1C2</td>
                                <td>R1C3</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="card mb-1">
            <div class="card-body p-2">
                <span class="badge bg-none text-dark shadow fs-5 border-2 border-secondary-subtle mb-1">On Going
                    Project</span>
                <div class="table-responsive overflow-y-auto" style="max-height: 200px;">
                    <table class="table table-sm table-bordered table-hover m-0">
                        <thead class="table-secondary sticky-top">
                            <tr>
                                <th scope="col">NO</th>
                                <th scope="col">Costumer</th>
                                <th scope="col">Model</th>
                                <th scope="col">Message</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="">
                                <td scope="row">R1C1</td>
                                <td>R1C2</td>
                                <td>R1C3</td>
                                <td>R1C2</td>
                                <td>R1C3</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                            <tr class="">
                                <td scope="row">Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                                <td>Item</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row justify-content-between align-items-center mx-0 px-0">
            <div class="col-auto px-0">
                <a href="" class="btn btn-primary border border-light">LOGOUT</a>
            </div>
            <div class="col-auto px-0">
                <a href="{{ route('main-menu') }}" class="btn btn-primary border border-light ms-auto">MAIN MENU</a>
            </div>
        </div>
    </div>
@endsection
