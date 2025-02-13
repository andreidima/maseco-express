<ul class="directory-tree list-unstyled">
    @foreach($nodes as $node)
        <li>
            <a href="{{ url('/file-manager-personalizat/' . $node['path']) }}">
                {{ $node['name'] }}
            </a>
            @if(!empty($node['children']))
                @include('fileManagerPersonalizat.directoryTree', ['nodes' => $node['children']])
            @endif
        </li>
    @endforeach
</ul>
