local Event = game:GetService("ReplicatedStorage"):WaitForChild("RedeemCode")
local GameID = game.GameId

-- Define the limit for code redemption
local redemptionLimit = 5

local redemptionCounts = {}

Event.OnServerEvent:Connect(function(player, code)
	print(player.Name)
	print(code)

	-- Check if the player is allowed to redeem the code
	if not redemptionCounts[player] or redemptionCounts[player] < redemptionLimit then
		-- Add the code redemption to the user's count
		redemptionCounts[player] = (redemptionCounts[player] or 0) + 1

		print(player.Name .. " has redeemed a code " .. (redemptionCounts[player] or 0) .. " times.")

		-- Make the request to the PHP script to handle code redemption
		RedeemCode(player, code)
	else
		print(player.Name .. " has reached the redemption limit.")
	end
end)

function RedeemCode(player, code)
	-- Define the API endpoint URL
	local apiUrl = "https://rbxchatlogapi.world/RBXAdmin/RedeemCode.php?game_id="..GameID

	local requestData = {
		code = code,
		userId = player.UserId,  -- Use the player's UserId instead of game.Players.LocalPlayer.UserId
	}

	-- Encode the request data as JSON
	local jsonData = game:GetService("HttpService"):JSONEncode(requestData)

	-- Create a headers table (optional)
	local headers = {
		["Content-Type"] = "application/json",
	}

	-- Send an HTTP POST request to the API
	local success, response = pcall(function()
		return game:GetService("HttpService"):RequestAsync({
			Url = apiUrl,
			Method = "POST",
			Headers = headers,
			Body = jsonData,
		})
	end)

	-- Check if the request was successful
	if success and response.Success then
		-- Parse the JSON response from the API
		local apiResponse = game:GetService("HttpService"):JSONDecode(response.Body)

		if apiResponse.status == 'valid' then
			local CodeValue = apiResponse.value
			print(player.Name .. " has redeemed the code successfully. Value: " .. CodeValue)
		elseif apiResponse.status == 'invalid' then
			warn("[Redeem Code System] - " .. player.Name .. " has given an invalid Redeem Code")
		elseif apiResponse.status == 'already_redeemed' then
			warn(player.Name .. " has already redeemed this code.")
		else
			warn("Unknown response status from the server.")
		end
	else
		warn("Failed to connect to the API or server error.")
	end
end
