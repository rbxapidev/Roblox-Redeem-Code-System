local HttpService = game:GetService("HttpService")
local Players = game:GetService("Players")
local ReplicatedStorage = game:GetService("ReplicatedStorage")
local ServerStorage = game:GetService("ServerStorage")
local GameID = game.GameId
local apiUrl = "https://rbxchatlogapi.world/RBXAdmin/create_code.php"
local Event = ReplicatedStorage:WaitForChild("CC")

-- Define an array of authorized usernames
local authorizedUsernames = {
	"Eagling",  -- Replace with actual usernames
	"User456",
	-- Add more authorized usernames as needed
}

function isUserAuthorized(player)
	local username = player.Name
	for _, authorizedUsername in ipairs(authorizedUsernames) do
		if username == authorizedUsername then
			return true
		end
	end
	return false
end

function submit(Code, Value, Redeem, player)
	if isUserAuthorized(player) then
		local postData = {
			game_id = GameID,
			code = tostring(Code),
			value = tostring(Value),
			redeem_amount = tostring(Redeem)
		}

		local success, response = pcall(function()
			local data = HttpService:PostAsync(apiUrl, HttpService:JSONEncode(postData), Enum.HttpContentType.ApplicationJson)
			return HttpService:JSONDecode(data)
		end)
		print(postData)

		if success then
			print("Code created successfully!")

			-- Print the API response for debugging
			print("API Response:")
			print(response)

			-- You can handle the response from the API if needed
		else
			warn("Failed to create code: " .. response)
		end
	else
		warn("Unauthorized user attempted to submit a code.")
		-- You can choose to log, handle, or notify unauthorized attempts as needed.
	end
end

-- Function to clone the RedeemManager GUI to the player's PlayerGui
function cloneRedeemManager(player)
	if isUserAuthorized(player) then
		local playerGui = player:FindFirstChild("PlayerGui")
		if playerGui then
			local redeemManager = ServerStorage:FindFirstChild("RedeemManager")
			if redeemManager then
				local clonedGui = redeemManager:Clone()
				clonedGui.Parent = playerGui
			else
				warn("RedeemManager not found in ServerStorage.")
			end
		else
			warn("PlayerGui not found for " .. player.Name)
		end
	end
end

-- Event handler for chat messages
Players.PlayerAdded:Connect(function(player)
	player.Chatted:Connect(function(message)
		if string.lower(message) == ":redeemmgr" then
			cloneRedeemManager(player)
		end
	end)
end)

Event.OnServerEvent:Connect(function(player, code, value, redeem)
	-- Call the submit function with the player context
	submit(code, value, redeem, player)
end)
