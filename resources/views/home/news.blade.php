@extends('layouts.app')
@section('page_name', 'Berita')
@section('content')
<section class="py-4">
    <div class="bg-[#F8F8F8] h-auto w-full py-6 px-10 flex flex-col gap-12 ">
        <div class="">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#DC3545]">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                </path>
                            </svg>
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('blog.news') }}"
                                class="ml-1 text-sm font-medium text-red-700 md:ml-2">
                                Berita
                            </a>

                        </div>
                    </li>

                </ol>
            </nav>
        </div>
        <div class="box w-full h-40 md:h-80 bg-[#DC3545] flex flex-col justify-center text-center rounded-lg gap-2">
            <p class="text-md md:text-2xl text-white">BLOG</p>
            <p class="text-lg md:text-6xl text-white font-semibold">Article & News</p>
        </div>
        <div class="article-section w-full flex flex-col justify-center text-center rounded-t-lg gap-2 ">
            <div class="heading flex flex-row justify-between">
                <p class="text-xl md:text-3xl text-[#DC3545] font-semibold">Latest Article</p>

            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4  h-auto mb-20 ">
                @foreach ($articles as $item)
                <div class="news flex flex-col  gap-3 rounded-lg bg-white shadow-lg ">
                    <div class="cover rounded-md">
                        <img src="{{ asset($item['image']) }}" alt="cover" class="rounded-t-lg ">
                    </div>
                    <div class="content flex flex-col gap-2 p-4">

                        <div class="title text-left ">
                            <p class="text-md md:text-xl text-[#DC3545] font-semibold line-clamp-1">{{$item['title']}}</p>
                        </div>
                        <div class="subtitle text-left">
                            <p class="text-gray-500 text-xs md:text-sm line-clamp-3">{{ $item['content'] }}</p>
                        </div>
                        <a class="text-left text-xl text-[#DC3545] hover:text-black mt-2 mb-4"
                            href="{{ route('blog.show', $item['slug']) }}">Read More</a>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</section>
@endsection
