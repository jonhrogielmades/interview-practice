@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$featurePage['title']" />
    <x-feature-overview :page="$featurePage" />
@endsection
