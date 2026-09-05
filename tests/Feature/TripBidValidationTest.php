<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\TripBidController;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TripBidValidationTest extends TestCase
{
    private function helper(): \Mockery\MockInterface
    {
        $helper = Mockery::mock('alias:App\\Helpers\\CustomHelper');
        $helper->shouldReceive('CheckUserExits')->andReturn((object) [
            'id' => 10, 'is_suspend' => false, 'name' => 'Test Driver',
        ]);
        $helper->shouldReceive('ErrorResponse')->andReturnUsing(
            fn ($message) => response()->json(['responseCode' => 0, 'responseText' => $message])
        );
        $helper->shouldReceive('SuccessWithoutData')->andReturnUsing(
            fn ($message) => response()->json(['responseCode' => 1, 'responseText' => $message])
        );

        return $helper;
    }

    public function test_invalid_id_is_rejected_before_any_bid_or_trip_query(): void
    {
        $this->helper();
        Mockery::mock('alias:App\\Models\\TripRequest')->shouldNotReceive('where');
        Mockery::mock('alias:App\\Models\\TripBid')->shouldNotReceive('where');

        foreach ([null, '', 'abc', '101abc', 0, -1, [101]] as $id) {
            $response = (new TripBidController)->bidTrip(Request::create('/', 'POST', ['tripId' => $id]));
            $this->assertSame([
                'responseCode' => 0, 'responseText' => 'A valid trip request ID is required.',
            ], $response->getData(true));
        }
    }

    public function test_missing_trip_request_cannot_create_or_update_a_bid(): void
    {
        $this->helper();
        $trip = Mockery::mock('alias:App\\Models\\TripRequest');
        $trip->shouldReceive('where')->once()->with('id', 101)->andReturnSelf();
        $trip->shouldReceive('first')->once()->andReturnNull();
        $bid = Mockery::mock('alias:App\\Models\\TripBid');
        $bid->shouldNotReceive('where');
        $bid->shouldNotReceive('insertGetId');
        Mockery::mock('alias:App\\Models\\Notification')->shouldNotReceive('insert');

        $response = (new TripBidController)->bidTrip(Request::create('/', 'POST', ['tripId' => 101]));
        $this->assertSame([
            'responseCode' => 0, 'responseText' => 'Trip request not found.',
        ], $response->getData(true));
    }

    public function test_valid_request_creates_bid_without_looking_up_a_ride(): void
    {
        $this->assertValidBid(false);
    }

    public function test_valid_request_updates_existing_bid(): void
    {
        $this->assertValidBid(true);
    }

    private function assertValidBid(bool $existing): void
    {
        $this->helper();
        Mockery::mock('alias:App\\Models\\Ride')->shouldNotReceive('where');
        $trip = Mockery::mock('alias:App\\Models\\TripRequest');
        $trip->shouldReceive('where')->once()->with('id', 101)->andReturnSelf();
        $trip->shouldReceive('first')->once()->andReturn((object) ['id' => 101, 'passenger_id' => 20]);
        $bid = Mockery::mock('alias:App\\Models\\TripBid');
        $bid->shouldReceive('where')->with('trip_id', 101)->andReturnSelf();
        $bid->shouldReceive('where')->with('driver_id', 10)->andReturnSelf();
        $bid->shouldReceive('first')->once()->andReturn($existing ? (object) ['id' => 5] : null);
        if ($existing) {
            $bid->shouldReceive('update')->once()->with(['proposed_fare' => 120])->andReturn(1);
            $bid->shouldNotReceive('insertGetId');
        } else {
            $bid->shouldReceive('insertGetId')->once()->with([
                'trip_id' => 101, 'driver_id' => 10, 'proposed_fare' => 120, 'status' => 0,
            ])->andReturn(5);
        }
        $user = Mockery::mock('alias:App\\Models\\User');
        $user->shouldReceive('where')->with('id', 20)->andReturnSelf();
        $user->shouldReceive('first')->andReturnNull();
        Mockery::mock('alias:App\\Models\\Notification')->shouldReceive('insert')->once()
            ->with(Mockery::on(fn ($data) => $data['trip_id'] === 101 && $data['bid_id'] === 5))
            ->andReturn(true);

        $response = (new TripBidController)->bidTrip(Request::create('/', 'POST', [
            'tripId' => 101, 'userId' => 10, 'proposedFare' => 120,
        ]));
        $this->assertSame(1, $response->getData(true)['responseCode']);
    }
}
