<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'date'            => $this->date?->toISOString(),
            'video_url'       => $this->video_url,
            'valuation'       => $this->valuation,
            'funding_target'  => $this->funding_target,
            'collected_funds' => $this->collected_funds,
            'investment_url'  => $this->investment_url,

            'likes_count'     => (int) ($this->likes_count ?? 0),
            'shares_count'    => (int) ($this->shares_count ?? 0),
            'investors_count' => (int) ($this->investors_count ?? 0),

            'owner'           => $this->whenLoaded('user',         fn ($u)    => (new UserResource($u))->resolve()),
            'categories'      => $this->whenLoaded('categories',   fn ($cats) => CategoryResource::collection($cats)->resolve()),
            'images'          => $this->whenLoaded('images',        fn ($imgs) => ProjectImageResource::collection($imgs)->resolve()),
            'status_timeline' => $this->whenLoaded('timelines',     fn ($tls)  => TimelineResource::collection($tls)->resolve()),
            'milestones'      => $this->whenLoaded('milestones',    fn ($ms)   => MilestoneResource::collection($ms)->resolve()),
            'shareholders'    => $this->whenLoaded('shareholders',  fn ($shs)  => ShareholderResource::collection($shs)->resolve()),
            'public_events'   => $this->whenLoaded('publicEvents',  fn ($evs)  => PublicEventResource::collection($evs)->resolve()),

            'posts' => [
                'total'   => (int) ($this->posts_count ?? 0),
                'preview' => $this->whenLoaded('postsPreview',    fn ($ps)  => PostResource::collection($ps)->resolve()),
            ],
            'comments' => [
                'total'   => (int) ($this->comments_count ?? 0),
                'preview' => $this->whenLoaded('commentsPreview', fn ($cms) => CommentResource::collection($cms)->resolve()),
            ],
        ];
    }
}
