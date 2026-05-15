<?php

namespace App\Livewire\Customer;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Exceptions\AuthException;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Support\IdempotencyKey;
use App\Livewire\Concerns\HandlesAuthException;
use Livewire\Component;

class SendMoney extends Component
{
    use HandlesAuthException;

    protected string $actor = 'customer';

    public string $step = 'recipient'; // recipient | amount | confirm | pin | threshold | receipt
    public string $recipientPhone = '';
    public string $recipientCountryCode = '269';
    public string $recipientName = '';
    public int $amount = 0;
    public string $amountInput = '';
    public string $description = '';
    public string $pin = '';
    public string $error = '';
    public string $idempotencyKey = '';
    public bool $showBeneficiaries = true;

    public ?array $receipt = null;
    public ?int $thresholdAmount = null;

    // True once the user has cleared the PENDING_CONFIRMATION control —
    // re-sent on the same idempotency key (spec 11.3).
    public bool $confirmationAcknowledged = false;
    public bool $pinLocked = false;

    public function mount(): void
    {
        $this->recipientCountryCode = (string) config('komopay.default_country_code');
        $this->idempotencyKey = IdempotencyKey::generate();
    }

    public function selectBeneficiary(string $phone, string $name): void
    {
        $this->recipientPhone = $phone;
        $this->recipientName = $name;
    }

    public function proceedToAmount(CustomerApi $api): void
    {
        $this->error = '';
        if (empty($this->recipientPhone)) {
            $this->error = __('customer.errors.recipient_required');
            return;
        }
        if (empty($this->recipientName)) {
            $needle = str_replace(' ', '', $this->recipientPhone);
            foreach ($api->beneficiaries(50) as $b) {
                if ($b['phoneNumber'] === $needle) {
                    $this->recipientName = $b['fullName'];
                    break;
                }
            }
            if (empty($this->recipientName)) {
                $this->recipientName = '+' . $this->recipientCountryCode . ' ' . $this->recipientPhone;
            }
        }
        $this->step = 'amount';
    }

    public function proceedToConfirm(): void
    {
        $this->error = '';
        $amount = (int) str_replace([' ', ','], '', $this->amountInput);
        if ($amount < 100) {
            $this->error = __('customer.errors.amount_min');
            return;
        }
        if ($amount > 500000) {
            $this->error = __('customer.errors.amount_max');
            return;
        }
        $this->amount = $amount;
        $this->step = 'confirm';
    }

    public function submitTransfer(CustomerApi $api): void
    {
        $this->dispatchTransfer($api);
    }

    public function submitWithPin(CustomerApi $api): void
    {
        $this->error = '';
        if ($this->pinLocked) {
            $this->error = __('customer.errors.pin_locked');
            return;
        }
        if (strlen($this->pin) < 4) {
            $this->error = __('customer.errors.pin_required');
            return;
        }
        $this->dispatchTransfer($api);
    }

    public function confirmThreshold(CustomerApi $api): void
    {
        $this->confirmationAcknowledged = true;
        // Priority Approval > PIN > Confirmation (spec 11.3): resubmit on the
        // same idempotency key — backend will either execute or re-emit PENDING_PIN.
        $this->dispatchTransfer($api);
    }

    private function dispatchTransfer(CustomerApi $api): void
    {
        $payload = [
            'recipientCountryCode'      => $this->recipientCountryCode,
            'recipientPhone'            => str_replace(' ', '', $this->recipientPhone),
            'amount'                    => $this->amount,
            'description'               => $this->description ?: null,
            'confirmationAcknowledged'  => $this->confirmationAcknowledged,
        ];
        if ($this->pin !== '') {
            // Raw PIN — server verifies. Never logged, never displayed.
            $payload['pin'] = $this->pin;
        }

        try {
            $result = $api->p2pTransfer($payload, $this->idempotencyKey);
        } catch (AuthException $e) {
            if ($e->errorCode() === 'AUTH_PIN_INVALID') {
                $this->pin = '';
                $this->step = 'pin';
                $this->error = __('customer.errors.pin_incorrect');
                return;
            }
            $this->error = $e->getMessage();
            return;
        } catch (BusinessException $e) {
            if ($e->errorCode() === 'AUTH_PIN_LOCKED') {
                $this->pin = '';
                $this->pinLocked = true;
                $this->step = 'pin';
                $this->error = __('customer.errors.pin_locked_3_attempts');
                return;
            }
            $this->error = $e->getMessage();
            return;
        } catch (KomopayException $e) {
            $this->error = $e->getMessage();
            return;
        } finally {
            // Never keep the raw PIN around once the call has returned.
            $this->pin = '';
        }

        switch ($result['outcome'] ?? null) {
            case 'PENDING_CONFIRMATION':
                $this->thresholdAmount = $result['matchedThresholdAmount'] ?? null;
                $this->step = 'threshold';
                return;

            case 'PENDING_PIN':
                $this->step = 'pin';
                return;

            case 'EXECUTED':
            default:
                $this->receipt = array_merge($result, ['recipientName' => $this->recipientName]);
                $this->step = 'receipt';
        }
    }

    public function resetForm(): void
    {
        $this->step = 'recipient';
        $this->recipientPhone = '';
        $this->recipientName = '';
        $this->amount = 0;
        $this->amountInput = '';
        $this->description = '';
        $this->pin = '';
        $this->error = '';
        $this->receipt = null;
        $this->thresholdAmount = null;
        $this->confirmationAcknowledged = false;
        $this->pinLocked = false;
        $this->idempotencyKey = IdempotencyKey::generate();
    }

    public function render(CustomerApi $api)
    {
        $beneficiaries = $api->beneficiaries(20);
        $balance = $api->balance();
        return view('livewire.customer.send-money', compact('beneficiaries', 'balance'))
            ->layout('layouts.customer', ['title' => __('customer.title.send_money')]);
    }
}
