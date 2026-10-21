@extends('errors.layout')
@section('kode', '403')
@section('judul', 'Akses Ditolak')
@section('pesan', $exception->getMessage() ?: 'Anda tidak memiliki hak akses ke halaman ini.')
