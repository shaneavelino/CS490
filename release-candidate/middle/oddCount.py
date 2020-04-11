def oddCount(numList):
	result = 0
	for i in numList:
		if i%2 == 1:
			result += 1
	return result

print(oddCount([2,4,6,8]))