<div class="d-flex align-items-center mr-4 badge">
  <div style="background-color: {{$tag->color}}; width: 10px; height: 10px; border-radius: 50%" class="mr-1"></div>
  <div>
    <a up-follow href="{{ action('TagController@show', $tag) }}" style="font-size: 0.8rem; color: #555">
      {{$tag->name}}
    </a>
  </div>
</div>
